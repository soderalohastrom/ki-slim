<?php
/*! \class Sales class.sales.php "class.sales.php"
 *  \brief used to work with sales.
 */
class Sales {
	/*! \fn obj __constructor($DB)
		\brief sales class constructor.
		\param	$DB db class object
		\return null
	*/
	function __construct($DB, $ENC=false) {
		$this->db 		= $DB;
		if($ENC) {
			$this->enc 	= $ENC;
		}
	}
	
	function options_packages($packageID) {
		$sql = "SELECT * FROM SellableEntities WHERE SellableEntities_active='1' ORDER BY SellableEntities_name ASC";
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		?><option value=""></option><?php
		foreach($snd as $dta):
			?><option value="<?php echo $dta['SellableEntities_id']?>" <?php echo (($packageID == $dta['SellableEntities_id'])? 'selected':'')?>><?php echo $dta['SellableEntities_name']?></option><?php
		endforeach;	
		return ob_get_clean();
	}
	
	function get_packageName($package_id) {
		$sql = "SELECT * FROM SellableEntities WHERE SellableEntities_id='".$package_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['SellableEntities_name'];		
	}
	
	function get_packageDescrip($package_id) {
		$sql = "SELECT * FROM SellableEntities WHERE SellableEntities_id='".$package_id."'";
		$snd = $this->db->get_single_result($sql);
		return $snd['SellableEntities_description'];		
	}
	
	function update_saleBalance($sale_id) {
		$ps_sql = "SELECT * FROM PersonsSales WHERE PersonsSales_id='".$sale_id."'";
		$ps_snd = $this->db->get_single_result($ps_sql);		
		$totalPrice = $ps_snd['PersonsSales_payment'];
		$contractID = $ps_snd['PersonsSales_ContractID'];
		
		$psp_sql = "SELECT * FROM PersonsPaymentInfo WHERE Contract_id='".$contractID."' AND PaymentInfo_Status >= 3 ORDER BY PaymentInfo_Execute ASC";
		//echo $psp_sql."\n";
		$psp_snd = $this->db->get_multi_result($psp_sql);
		$payTally[] = 0.00;
		foreach($psp_snd as $payment):
			$payTally[] = $this->enc->decrypt($payment['PaymentInfo_Amount']);
		endforeach;
		//print_r($payTally);
		$paidTotal = array_sum($payTally);
		$balance = $totalPrice - $paidTotal;
		
		$upd_sql = "UPDATE PersonsSales SET PersonsSales_balance='".$balance."' WHERE PersonsSales_id='".$sale_id."'";
		//echo $upd_sql;
		$upd_snd = $this->db->mysqli->query($upd_sql);
		return $balance;		
	}
	
	function render_clientSales($person_id) {
		$sql = "SELECT * FROM PersonsSales LEFT JOIN SellableEntities ON SellableEntities.SellableEntities_id=PersonsSales.PersonsSales_packageID WHERE Persons_Person_id='".$person_id."' ORDER BY PersonsSales_dateCreated DESC";
		$snd = $this->db->get_multi_result($sql);
		foreach($snd as $dta):
			$this->update_saleBalance($dta['PersonsSales_id']);
		endforeach;
		
		$snd = $this->db->get_multi_result($sql);
		ob_start();
		
		if(isset($snd['empty_result'])) {
			?>
            <div style="margin:30px; text-align:center;">NO SALE RECORDS FOUND</div>
            <?php
		} else {			
			?>
            <table class="table table-condensed table-striped" id="sales_table" width="100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th width="100">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php			
			foreach($snd as $dta):
			//$this->update_saleBalance($dta['PersonsSales_id']);
			?>	
			<tr>
				<td><?php echo date("m/d/y", $dta['PersonsSales_dateCreated'])?></td>
				<td><span style="white-space:nowrap; overflow:auto; text-overflow:ellipsis;"><?php echo $dta['SellableEntities_name']?></span></td>
                <td><?php echo number_format($dta['PersonsSales_payment'], 2)?></td>
                <td id="blanaceFor_<?php echo $dta['PersonsSales_id']?>"><?php echo number_format($dta['PersonsSales_balance'], 2)?></td>
				<td><a href="javascript:viewSale('<?php echo $dta['PersonsSales_id']?>');" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View"><i class="la la-search-plus"></i></a></td>
			</tr>
            <?php
			endforeach;
			?>
            </tbody>
            </table>
            <?php
		}
		return ob_get_clean();	
	}
	
	function get_salePayments($saleID, $personID, $ENC) {		
		$sql = "SELECT * FROM PersonsSales WHERE PersonsSales_id='".$saleID."'";
		$snd = $this->db->get_single_result($sql);
		$contractID = $snd['PersonsSales_ContractID'];
		if($contractID == 0) {
			$c_sql = "SELECT * FROM PersonsContract WHERE Person_id='".$personID."' AND Contract_status != '3'";
			$c_snd = $this->db->get_single_result($c_sql);
			$contractID = $c_snd['Contract_id'];			
		}
		
		$cp_sql = "SELECT * FROM PersonsPaymentInfo WHERE Contract_id='".$contractID."'";
		//echo $cp_sql."<br>";
		$cp_snd = $this->db->get_multi_result($cp_sql);
		$runningTotal[] = 0.00;
		if (isset($cp_snd['empty_result'])) {
			$runningTotal[] = 0.00;
			$error = 1;
				
			$totalFound = @array_sum($runningTotal);
			$display = '<i class="la la-info-circle" data-toggle="m-tooltip" data-original-title="This sale has no payments associated with it"></i> 0.00';
			$totalRefund = @array_sum($runningRefund);
			$refundDisplay = number_format($totalRefund, 2);
			$isRefund = 0;	
		} else {
			$isRefund = 0;
			foreach($cp_snd as $cp_dta) {
				$baseTotal[] = $ENC->decrypt($cp_dta['PaymentInfo_Amount']);
				if($cp_dta['PaymentInfo_Status'] == 4 && ($cp_dta['PaymentInfo_isRefund'] != 1)) {
					$runningTotal[] = $ENC->decrypt($cp_dta['PaymentInfo_Amount']);
				}
				if($cp_dta['PaymentInfo_Status'] == 4 && ($cp_dta['PaymentInfo_isRefund'] == 1)) {
					$runningRefund[] = $ENC->decrypt($cp_dta['PaymentInfo_Amount']);
					$isRefund = 1;
				}
				if($cp_dta['PaymentInfo_Status'] == 3 && ($cp_dta['PaymentInfo_isRefund'] != 1)) {
					$processedTotal[] = $ENC->decrypt($cp_dta['PaymentInfo_Amount']);
				}
				if($cp_dta['PaymentInfo_Status'] == 4 && ($cp_dta['PaymentInfo_isRefund'] != 1)) {
					$collectedTotal[] = $ENC->decrypt($cp_dta['PaymentInfo_Amount']);
				}
			}
			$error = 0;
			$totalFound = @array_sum($runningTotal);
			$totalProcessed = @array_sum($processedTotal);
			$display = number_format($totalFound, 2);
			$totalRefund = @array_sum($runningRefund);			
			$refundDisplay = number_format($totalRefund, 2);
			$baseTotal = @array_sum($baseTotal);
			$collected = @array_sum($collectedTotal);
		}
		
		return array(
			'base'		=>	$baseTotal,			
			'total'		=>	$totalFound,
			'display'	=>	$display,
			'refund'	=>	$totalRefund,
			'rdisplay'	=>	$refundDisplay,
			'iSrefund'	=>	$isRefund,
			'processed'	=>	$totalProcessed,
			'paid'		=>	$collected
		);		
	}
	
	function getPaymentType($typeID) {
		switch($typeID) {
			case 1:
			$type = 'Credit Card';
			break;
			
			case 2:
			$type = 'Electronic Check';
			break;
			
			case 3:
			$type = 'Wire Transfer';
			break;
		}
		return $type;
	}
	
	function getPaymentStatus($statusID) {
		switch($statusID) {
			case 1:
			$statusCode = '<span class="m-badge m-badge--info m-badge--wide">PENDING</span>';
			break;
			
			case 2:
			$statusCode = '<span class="m-badge m-badge--warning m-badge--wide">SUBMITTED</span>';
			break;
			
			case 3:
			$statusCode = '<span class="m-badge m-badge--brand m-badge--wide">PROCESSED</span>';
			break;
			
			case 4:
			$statusCode = '<span class="m-badge m-badge--success m-badge--wide">PAID</span>';
			break;
			
			case 5:
			$statusCode = '<span class="m-badge m-badge--danger m-badge--wide">NSF</span>';
			break;
			
			case 6:
			$statusCode = '<span class="m-badge m-badge--secondary m-badge--wide">VOID</span>';
			break;
		}
		return $statusCode;		
	}
	
	function getContractStatus($statusID) {
		switch($statusID) {
			case 1:
			$statusCode = '<span class="m-badge m-badge--info m-badge--wide">PENDING</span>';
			break;
			
			case 2:
			$statusCode = '<span class="m-badge m-badge--warning m-badge--wide">SIGNED</span>';
			break;
			
			case 3:
			$statusCode = '<span class="m-badge m-badge--success m-badge--wide">PROCESSED</span>';
			break;
		}
		return $statusCode;		
	}
	
	function options_PayStatusSelect($current) {
		ob_start();		
		?>
        <option value="1" <?php echo (($current == 1)? 'selected':'')?>>PENDING</option>
        <option value="2" <?php echo (($current == 2)? 'selected':'')?>>SUBMITTED</option>
		<option value="3" <?php echo (($current == 3)? 'selected':'')?>>PROCESSED</option>
        <option value="4" <?php echo (($current == 4)? 'selected':'')?>>PAID</option>
        <option value="5" <?php echo (($current == 5)? 'selected':'')?>>NSF</option>
        <option value="6" <?php echo (($current == 6)? 'selected':'')?>>VOID</option>
		<?php
		$userSelect = ob_get_clean();
		return $userSelect;
	}
	
	
}
?>