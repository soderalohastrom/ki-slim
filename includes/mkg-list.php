<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("class.marketing.php");
include_once("class.record.php");
$marketing = new Marketing();
$RECORD = new Record($DB);
$list_id = (isset($_POST['list_id'])) ? $_POST['list_id'] : (array_key_exists('params', $pageParamaters) && count($pageParamaters['params']) > 0 && is_numeric($pageParamaters['params'][0]) ? $pageParamaters['params'][0] : 0);
?>
<script src="/assets/app/js/marketing.js" type="text/javascript"></script>
<style type="text/css">
.list-group a {
	cursor:pointer;
}
.list-group a:hover {
	background-color:#ececec;
}
</style>

<div class="m-content">
<?php
$form_errors  = array();

//process form submission
require_once("mkg-list.submit.php");

$mcat_id = 0;

//get list data
if($list_id > 0)
{
    $query = $DB->mysqli->query("SELECT * FROM MarketingLists WHERE MarketingLists_id = '".$DB->mysqli->escape_string($list_id)."' LIMIT 1") or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);
    $data  = $query->fetch_assoc();
	$mcat_id = $data['MarketingLists_category'];
}

//get categories
$mcat_query = $DB->mysqli->query("SELECT * FROM MarketingListCategories ORDER BY MarketingListCategories_order") or die('Line: '.__LINE__ .' - '.$DB->mysqli->error);

if(isset($_POST['MarketingLists_category'])) {
	$mcat_id = $_POST['MarketingLists_category'];
}

//get counts
$groups       = ($_POST['submitted'] == 1) ? ($_POST['list_groups'] != '' ? explode('|', $_POST['list_groups']) : array()) : explode('|', $data['MarketingLists_groups']);
$assoc_groups = array();

foreach($groups as $gid)
{
    $sql    = 'SELECT `Groups_name` FROM `Groups` WHERE `Groups_id` = ' .$DB->mysqli->escape_string($gid) . ' LIMIT 1 ';
   $query  = $DB->mysqli->query($sql);
    
    if($query->num_rows < 1)
    {
        continue;
    }
    
    $row = $query->fetch_assoc();
    $assoc_groups[$gid] = $row['Groups_name'];
}

$counts = $marketing->get_group_counts($groups);

$email_count  = $counts['emails'];
$record_count = $counts['records'];
$fax_count    = $counts['faxes'];

//get CRM group list
$grp_sql   = "SELECT * FROM Groups WHERE Groups_active = '1' ORDER BY Groups_name";
$grp_query = $DB->mysqli->query($grp_sql);
?>

<input type="hidden" id="page_id" value="marketinglists_detail" />

<?php if(count($form_errors) > 0): ?>
<div id="form_errors" class="alert alert-danger alert-dismissible fade show">
	<button type="button" class="close" data-dismiss="alert"></button>
	<h4 class="alert-heading">Please correct the following:</h4>
    <?php foreach($form_errors as $err): ?>
    <div><?php echo $err?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if(strlen($success) > 0): ?>
<div id="form_success" class="alert alert-success alert-dismissible fade show">
	<button type="button" class="close" data-dismiss="alert"></button>
	<i class="la la-check"></i> <?php echo $success?>
	<div style="margin-top:5px;"><button type="button" class="btn btn-sm btn-default" onclick="document.location='/mkg-lists'"><i class="la la-arrow-left"></i>&nbsp;Back to Marketing Lists</button></div>
</div>
<?php endif; ?>

<?php if(strlen($failure) > 0): ?>
<div id="form_failure" class="alert alert-danger alert-dismissible fade show">
	<button type="button" class="close" data-dismiss="alert"></button>
    <i class="la la-exclamation"></i> <?php echo $failure?>
</div>
<?php endif; ?>
							
<form id="list_form" name="list_form" method="post" action="/mkg-list/<?php echo $list_id?>" class="m-form m-form--fit m-form--label-align-right">
	<input type="hidden" value="1" name="submitted" />
	<input type="hidden" value="<?php echo $list_id?>" name="list_id" id="list_id" />
	
	<div class="m-portlet m-portlet--mobile">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<h3 class="m-portlet__head-text">
						<i class="flaticon-list-3"></i>
						<?php echo ($list_id == 0 ? 'New' : 'Edit')?> Marketing List
					</h3>
				</div>
			</div>
		</div>
		
		<div class="m-portlet__body">
		
		<div class="form-group m-form__group row">
			<label for="MarketingLists_name" class="col-form-label col-lg-2">
				List Name
			</label>
			<div class="col-lg-4">
				<input class="form-control m-input" type="text" value="<?php echo ((isset($_POST['MarketingLists_name']))?$_POST['MarketingLists_name']:$data['MarketingLists_name'])?>" id="MarketingLists_name" name="MarketingLists_name">
			</div>
		</div>
		
		<div class="form-group m-form__group row">
			<label for="MarketingLists_category" class="col-form-label col-lg-2">
				Category
			</label>
			<div class="col-lg-4">
				<select name="MarketingLists_category" id="MarketingLists_category" class="form-control m-input">
					<option value="0">--select--</option>
					<?php  while($mcat_data = $mcat_query->fetch_assoc()) { ?>
						<option value="<?php echo $mcat_data['MarketingListCategories_id']?>"<?php echo ($mcat_id == $mcat_data['MarketingListCategories_id'] ? ' selected':'')?>><?php echo $mcat_data['MarketingListCategories_name']?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		
		<div class="m-form__group form-group row">
			<label for="MarketingLists_active" class="col-form-label col-lg-2" style="padding-top:.35rem;">
				Status
			</label>
			<div class="col-lg-4">
				<div class="m-radio-inline">
				<label class="m-radio">
					<input type="radio" name="MarketingLists_active" value="1"<?php echo ((isset($_POST['submitted']) && $_POST['MarketingLists_active'] == '1') || (!isset($_POST['submitted']) && $data['MarketingLists_active'] == 1) || (!isset($_POST['submitted']) && $list_id == 0)) ? ' checked="checked" ':''; ?> />
					Active
					<span></span>
				</label>
				<label class="m-radio">
					<input type="radio" name="MarketingLists_active" value="0"<?php echo ((isset($_POST['submitted']) && $_POST['MarketingLists_active'] == '0') || (!isset($_POST['submitted']) && $data['MarketingLists_active'] == 0 && $list_id != 0)) ?' checked="checked" ':''; ?> />
					Inactive
					<span></span>
				</label>
				</div>
			</div>
		</div>
		
		<div class="m-form__group form-group row">
			<label for="" class="col-form-label col-lg-2" style="padding-top:0;">Total Records</label>
			<div class="col-lg-4 info_label">
				<span id="rec_count" class="m-badge m-badge--info"><?php echo $record_count?></span>
				<span id="count_spinner" style="display:none;">
                    <div class="m-loader m-loader--brand"></div>
                </span>
			</div>
		</div>
		
		<div class="m-form__group form-group row">
			<label for="" class="col-form-label col-lg-2" style="padding-top:0;">Marketable Emails</label>
			<div class="col-lg-4 info_label">
				<span id="email_count" class="m-badge m-badge--success"><?php echo $email_count?></span>
				<!--<span id="count_email_spinner" style="display:none;">
                    <div class="m-loader m-loader--brand"></div>
                </span>-->
			</div>
		</div>
	  
		<input type="hidden" id="list_groups" name="list_groups" value="<?php echo implode('|', array_keys($assoc_groups))?>" /><br />
		
		<div class="row">
		
		<div class="col-sm-6">
		<div class="m-portlet m-portlet--success m-portlet--head-solid-bg">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<span class="m-portlet__head-icon">
							<i class="la la-list-alt"></i>
						</span>
						<h3 class="m-portlet__head-text">
							Included Groups
						</h3>
					</div>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-portlet__nav-item">
							<a href="javascript:void(clear_groups())" class="m-portlet__nav-link btn btn-secondary m-btn m-btn--icon m-btn--icon-only m-btn--pill" title="remove all">
								<i class="la la-remove"></i>
							</a>
							<!--<a href="#" class="m-portlet__nav-link btn btn-sm btn-light m-btn" onclick="clear_groups();">
								<i class="la la-minus-circle"></i>&nbsp;Remove All
							</a>-->
						</li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<div class="list-group" id="assoc_groups">
					<?php foreach($assoc_groups as $gid => $name): ?>
						<?php $group_name = (strlen($name) > 0 ? $name : 'Group ID: '.$gid); ?>
						<a class="list-group-item assoc_item" data-gid="<?php echo $gid?>" data-name="<?php echo str_replace('"', '&quot;', $group_name)?>" title="Click to remove this group from the marketing list">
							<i class="la la-remove"></i>&nbsp;<?php echo $group_name?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		</div>
		
		<div class="col-sm-6">
		<div class="m-portlet m-portlet--info m-portlet--head-solid-bg">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<span class="m-portlet__head-icon">
							<i class="la la-list"></i>
						</span>
						<h3 class="m-portlet__head-text">
							Available Groups
						</h3>
					</div>
				</div>
			</div>
			<div class="m-portlet__body">
           
<div class="accordion" id="group_list">
<?php
$mlg_sql = "SELECT `Groups_catID`, `Groups_Category_Name`, `Groups_Category_Order` FROM `Groups_Categories` WHERE `Groups_catID` != 0 ORDER BY `Groups_Category_Order` ASC";
$mlg_snd = $DB->get_multi_result($mlg_sql);
foreach($mlg_snd as $mlg_dta):
	$grp_sql   = "SELECT `Groups_name`, `Groups_id`,`Groups_source` FROM `Groups` WHERE `Groups_active` = '1' AND `Groups_source` IN (1, 2) AND `Groups_catID`='".$mlg_dta['Groups_catID']."' ORDER BY Groups_name";
	$grp_snd = $DB->get_multi_result($grp_sql);
	if($grp_snd['empty_result'] != 1):
		$listCat_ID = 'ListCat_'.$mlg_dta['Groups_catID'];
		?>
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0" data-toggle="collapse" data-target="#<?php echo $listCat_ID?>" aria-expanded="false" aria-controls="collapseOne" style="color:#000; cursor:pointer; padding:.65 rem 1.25 rem; font-size:1rem; font-weight:400; line-height:1.25;">
                    <?php echo strtoupper($mlg_dta['Groups_Category_Name'])?>
                </h5>
            </div>
    
            <div id="<?php echo $listCat_ID?>" class="collapse" aria-labelledby="headingOne" data-parent="#group_list">
                <div class="card-body" style="padding:0rem;">
                	<ul class="list-group">                    
					<?php
					 foreach($grp_snd as $grp):
                        $group_name = (strlen($grp['Groups_name']) > 0 ? $grp['Groups_name'] : 'Group ID: '.$grp['Groups_id']); 
                        ?>
                        <li class="list-group-item">
                            <?php if($grp['Groups_source'] == 2): ?>
                            <a href="/fullsearch/<?php echo $grp['Groups_id']?>" class="btn btn-metal m-btn m-btn--icon m-btn--icon-only pull-right" title="Click to view this list." target="_blank">
								<i class="la la-list"></i>
							</a>
                            <?php endif; ?>
                            <a href="javascript:;" class="btn btn-success m-btn m-btn--icon m-btn--icon-only group_row" data-gid="<?php echo $grp['Groups_id']?>" data-name="<?php echo str_replace('"', '&quot;', $group_name)?>" title="Click to add this group to the marketing list">
								<i class="la la-plus"></i>
							</a>
                            &nbsp;<?php echo $group_name?>
                        </li>                        
                        <?php
                    endforeach;
                    ?>                    
                    </ul>                   
                </div>
            </div>
        </div>
        <?php
	endif;
endforeach;


$mlg_sql = "SELECT DISTINCT (`Groups_createdBy`) FROM `Groups` WHERE `Groups_source` IN (1, 2) AND `Groups_catID`='0'";
$mlg_snd = $DB->get_multi_result($mlg_sql);
foreach($mlg_snd as $mlg_dta):
	$grp_sql   = "SELECT `Groups_name`, `Groups_id`, `Groups_source` FROM `Groups` WHERE `Groups_active` = '1' AND `Groups_source` IN (1, 2) AND `Groups_catID`='0' AND `Groups_createdBy`='".$mlg_dta[0]."' ORDER BY `Groups_name`";
	$grp_snd = $DB->get_multi_result($grp_sql);
	if($grp_snd['empty_result'] != 1):
		$listCat_ID = 'ListCat_0_'.$mlg_dta[0];
		?>
        <div class="card">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0" data-toggle="collapse" data-target="#<?php echo $listCat_ID?>" aria-expanded="false" aria-controls="collapseOne" style="color:#000; cursor:pointer; padding:.65 rem 1.25 rem; font-size:1rem; font-weight:400; line-height:1.25;">
                   CUSTOM LISTS by <?php echo $RECORD->get_userName($mlg_dta[0])?>
                </h5>
            </div>
    
            <div id="<?php echo $listCat_ID?>" class="collapse" aria-labelledby="headingOne" data-parent="#group_list">
                <div class="card-body" style="padding:0rem;">
                	<ul class="list-group">                    
					<?php
				  foreach($grp_snd as $grp):
						 $group_name = (empty($grp['Groups_name']) ? $grp['Groups_name'] : 'Group ID: '.$grp['Groups_id']); 
                        ?>
                        <li class="list-group-item">
                            <?php if($grp['Groups_source'] == 2): ?>
                            <a href="/fullsearch/<?php echo $grp['Groups_id']?>" class="btn btn-metal m-btn m-btn--icon m-btn--icon-only pull-right" title="Click to view this list." target="_blank">
								<i class="la la-list"></i>
							</a>
                            <?php endif; ?>
                            <a href="javascript:;" class="btn btn-success m-btn m-btn--icon m-btn--icon-only group_row" data-gid="<?php echo $grp['Groups_id']?>" data-name="<?php echo str_replace('"', '&quot;', $group_name)?>" title="Click to add this group to the marketing list">
								<i class="la la-plus"></i>
							</a>
                            &nbsp;<?php echo $group_name?>
                        </li>                        
                        <?php
                    endforeach;
                    ?>                    
                    </ul>                   
                </div>
            </div>
        </div>
        <?php
	endif;
endforeach;



?>   
</div>           
					</div>
			</div>
		</div>
		</div>
		
		
	</div>
		
	</div>
	
	<div class="m-portlet__foot m-portlet__no-border m-portlet__foot--fit">
        <div class="m-form__actions m-form__actions--solid">
            <div class="row">
				<div class="col-12">
					<a href="/mkg-lists" class="btn btn-secondary" style="margin-right:5px;"><i class="la la-arrow-left"></i>&nbsp;Back to Marketing Lists</a>
                    <button type="submit" class="btn btn-success">
						<i class="la la-save"></i>&nbsp;
                        Save Marketing List
                    </button>
				</div>
			</div>
        </div>
    </div>
	<!--
	<div class="m-portlet__foot">
		<div class="row align-items-center">
			<div class="col-lg-12">
				<button type="button" class="btn btn-default" style="margin-right:5px;" onclick="document.location='/mkg-lists'"><i class="la la-arrow-left"></i>&nbsp;Back to Marketing Lists</button>
				<button type="submit" class="btn btn-success"><i class="la la-save"></i>&nbsp;Save Marketing List</button>
			</div>
		</div>
	</div>
	-->
</div>
</form>
													
</div>