<?php
class recordLinks extends Record {
	function __construct($DB) {
		$this->db = $DB;
		$this->networks = array(
			array(
				'name'	=> 	'facebook.com',
				'icon'	=>	'fa fa-facebook-square',
				'net'	=>	'Facebook'
			),
			array(
				'name'	=> 	'twitter.com',
				'icon'	=>	'fa fa-twitter-square',
				'net'	=>	'Twitter'
			),
			array(
				'name'	=> 	'instagram.com',
				'icon'	=>	'fa fa-instagram',
				'net'	=>	'Instagram'
			),
			array(
				'name'	=> 	'linkedin.com',
				'icon'	=>	'fa	fa-linkedin-square',
				'net'	=>	'LinkedIn'
			),
			array(
				'name'	=> 	'youtube.com',
				'icon'	=>	'fa	fa-youtube-square',
				'net'	=>	'YouTube'
			)
		);
		
	}
	
	function render_sideBarNav($pid) {
		$activeLinks = $this->get_activeLinks($pid);
		?>
        <li class="m-nav__item">                            
            <a href="#" data-toggle="modal" data-target="#personLinksModal" class="m-nav__link">
                <i class="m-nav__link-icon flaticon-list-1"></i>
                <span class="m-nav__link-text">
                    Web Links &amp; Connections
                </span>
                <span class="m-nav__link-badge" style="margin-right:5px;">
                    <?php if($activeLinks > 0): ?>
                    <span class="m-badge m-badge--info m-badge--rounded">
                        <?php echo $activeLinks?>
                    </span>
                    <?php endif; ?>
                </span>
            </a>                      
        </li>
        <li class="m-nav__item"> 
            <div class="m-nav__link text-right">                       	
            <?php echo $this->render_networkButtons($pid)?>
            </div>                            
        </li>
        <?php		
	}
	
	function render_networkButtons($pid) {
		$sql = "SELECT * FROM PersonsLinks WHERE Person_id='".$pid."' ORDER BY DateAdded DESC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		if($snd['empty_result'] != 1):
		foreach($snd as $dta):
			$parsedURL = parse_url($dta['LinkURL']);
			$cleanHost = str_replace("www.", "", $parsedURL['host']);
			foreach($this->networks as $network):
				if($network['name'] == $cleanHost):
					$icon = $network['icon'];
					?><a href="<?php echo $dta['LinkURL']?>" class="btn btn-outline-metal m-btn m-btn--icon m-btn--icon-only" data-toggle="m-popover" data-placement="top" data-content="<?php echo $network['net']?>" data-original-title="" title="" target="_blank"><i class="<?php echo $icon?>"></i></a>&nbsp;<?php
				endif;
			endforeach;		
		endforeach;
		endif;
		return ob_get_clean();		
	}
	
	function get_activeLinks($pid) {
		$sql = "SELECT count(*) as count FROM PersonsLinks WHERE Person_id='".$pid."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['count'];	
		
	}
	
	function render_personLinkList($pid) {
		$sql = "SELECT * FROM PersonsLinks WHERE Person_id='".$pid."' ORDER BY DateAdded DESC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if($snd['empty_result'] != 1):
		foreach($snd as $dta):
			?>
            <div class="m-list-timeline__item">
                <span class="m-list-timeline__badge m-list-timeline__badge--success"></span>
                <span class="m-list-timeline__icon <?php echo $this->get_networkIcon($dta['LinkURL'])?>"></span>
                <span class="m-list-timeline__text">
                    <?php echo $dta['LinkURL']?>&nbsp;<a href="<?php echo $dta['LinkURL']?>" target="_blank"><i class="fa fa-share"></i></a>
                </span>
                <span class="m-list-timeline__time">                    
                    <a href="javascript:removeLink(<?php echo $dta['Link_id']?>);"><i class="fa fa-remove"></i></a>
                </span>
            </div>
            <?php			
		endforeach;
		endif;		
	}
	
	function get_networkIcon($url) {
		$parsedURL = parse_url($url);

		$cleanHost = str_replace("www.", "", $parsedURL['host']);
		foreach($this->networks as $network):
			if($network['name'] == $cleanHost):
				$icon = $network['icon'];
			endif;
		endforeach;
		
		if(!isset($icon)):
			$icon = 'fa fa-external-link-square';
		endif;						
		return $icon;
	}
	
	function render_personLinksModal($pid) {
		global $SESSION;
		?>
<div class="modal fade" id="personLinksModal" role="dialog" aria-labelledby="personLinksModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="personLinksModalLabel">Record Links</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
            <form class="m-form" id="personLinksForm" action="javascript:addNewLink()">
			<?php echo $SESSION->renderToken()?>	
            <input type="hidden" name="LinkPersonID" id="LinkPersonID" value="<?php echo $pid?>" />		
            <div class="modal-body">


<div id="LinkFormArea" style="display:none;">          
    <div class="form-group m-form__group">
        <label for="">
            Link URL
        </label>
        <span class="input-group">
            <span class="input-group-addon">URL</span>
            <input type="text" class="form-control m-input" name="LinkURL" id="LinkURL"  placeholder="Enter URL" required="required" autocomplete="new-pass">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default">Add</button>
            </span>
        </span>            
        <span class="m-form__help">
            please enter the entire URL including the http or https
        </span>
    </div>
</div>

<div class="m-list-search">
    <div class="m-list-search__results">        
        <span class="m-list-search__result-category m-list-search__result-category--first">
            <a href="javascript:$('#LinkFormArea').toggle();" class="pull-right"><i class="fa fa-plus"></i></a>
            Current Links
        </span>
	</div>
</div>

<div class="m-list-timeline">
    <div class="m-list-timeline__items" id="PersonLinkList">
        <?php echo $this->render_personLinkList($pid)?>
    </div>
</div>            
        
        
                  
            
            </div>
			<div class="modal-footer" style="display:block;">
            	<div class="row">
                	<div class="col-4">
		            	<button type="button" class="btn btn-outline-info" onclick="$('#LinkFormArea').toggle();" style="float:left;">Add Link <i class="fa fa-plus"></i></button>
					</div>
                    <div class="col-8 text-right">                       
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						<!-- <button type="button" class="btn btn-primary" onclick="">Save</button> -->
					</div>
				</div>                                            
			</div>
            </form
		></div>
	</div>
</div>
<script>
function addNewLink() {
	var formData = $('#personLinksForm').serializeArray();
	$.post('/ajax/links.php?action=add', formData, function(data) {
		if(data.success) {
			$('#LinkURL').val('');
			refreshLinkList();
		} else {
			toastr.error(data.error, '');	
		}
	}, "json");
}
function refreshLinkList() {
	$.post('/ajax/links.php?action=list', {
		pid: <?php echo $pid?>,
		kiss_token: '<?php echo $SESSION->createToken()?>'
	}, function(data) {
		$('#PersonLinkList').html(data);
	});
}
function removeLink(id) {
	var choice = confirm('Are you sure you want to remove this link?');
	if(choice) {
		$.post('/ajax/links.php?action=remove', {
			lid: id,
			kiss_token: '<?php echo $SESSION->createToken()?>'
		}, function(data) {
			refreshLinkList();
		});			
	}
}
</script>
        
        <?php		
	}
}
?>