<?php
class recordAddress extends Record {
	function render_addressStack($person_id) {
		//print_r($this);
		?><div class="m-stack m-stack--ver m-stack--general m-stack--demo" style="margin-bottom:10px;"><?php
		$sql = "SELECT * FROM Addresses WHERE Person_id='".$person_id."' ORDER BY isPrimary DESC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if(!isset($snd['empty_result'])):
			foreach($snd as $dta):
			?>
            <div class="m-stack__item address-select-block" data-id="<?php echo $dta['Address_id']?>">
                <div>
				<?php echo $dta['Street_1']?><br />
				<?php echo $dta['City']?> <?php echo $dta['State']?> <?php echo $dta['Postal']?> <?php echo $dta['Country']?>&nbsp;
                <span class="m--font-<?php echo (($dta['GeoLocationStatus'] == 200)? 'success':'danger')?>" title="<?php echo (($dta['GeoLocationStatus'] == 200)? 'This record is geo-located':'This record is NOT geo-located')?>"><i class="fa fa-map-marker"></i></span></div>
            </div>
            <?php
			endforeach;
		endif;
		?>
        </div>
		<?php
	}
	
	function render_addressStackWithTopMatches($person_id) {
		include_once("class.searching.php");
		$SEARCHING = new Searching($this->db, $this);
		
		//print_r($this);
		?><div class="m-stack m-stack--ver m-stack--general m-stack--demo" style="margin-bottom:10px;"><?php
		$sql = "SELECT * FROM Addresses WHERE Person_id='".$person_id."' ORDER BY isPrimary DESC";
		//echo $sql;
		$snd = $this->db->get_multi_result($sql);
		if(!isset($snd['empty_result'])):
			foreach($snd as $dta):
			?>
            <div class="m-stack__item address-topmatches-block" data-id="<?php echo $dta['Address_id']?>">
                <div>
				<?php echo $dta['Street_1']?><br />
				<?php echo $dta['City']?> <?php echo $dta['State']?> <?php echo $dta['Postal']?> <?php echo $dta['Country']?>&nbsp;
                <span class="m--font-<?php echo (($dta['GeoLocationStatus'] == 200)? 'success':'danger')?>" title="<?php echo (($dta['GeoLocationStatus'] == 200)? 'This record is geo-located':'This record is NOT geo-located')?>"><i class="fa fa-map-marker"></i></span><br />
                <?php echo $SEARCHING->quickMatches($person_id, $dta['Address_id'])?> 
                </div>
            </div>
            <?php
			endforeach;
		endif;
		?>
        </div>
		<?php
	}

	
	
	
	
	
}