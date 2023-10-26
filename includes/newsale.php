<?php
include_once("class.record.php");
include_once("class.sales.php");
include_once("class.sessions.php");
include_once("class.encryption.php");

$RECORD = new Record($DB);
$SALES = new Sales($DB);

$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

//print_r($pageParamaters);
$PERSON_ID = $pageParamaters['params'][0];
$p_sql = "SELECT * FROM Persons WHERE Person_id='".$PERSON_ID."'";
$P_DATA = $DB->get_single_result($p_sql);

$SALE_ID = $pageParamaters['params'][1];
//echo "SALE ID: ".$SALE_ID;
if(($SALE_ID != 0) && ($SALE_ID != '')) {
	// EDITING EXISTING SALE //
	$ps_sql = "SELECT * FROM PersonsSales WHERE PersonsSales_id='".$SALE_ID."'";
	$ps_snd = $DB->get_single_result($ps_sql);
	$SALE_DATA = $ps_snd;
	$showPath = false;
	//echo "DATE CREATED:".$SALE_DATA['PersonsSales_dateCreated'];
	$dateCreated = date("m/d/Y", $SALE_DATA['PersonsSales_dateCreated']);
	$saleOffice = array($SALE_DATA['Offices_Offices_id']);
	$packageID = $SALE_DATA['PersonsSales_packageID'];
	$basePrice = $SALE_DATA['PersonsSales_basePrice'];
	$taxesPrice = $SALE_DATA['PersonsSales_taxes'];
	$totalPrice = $SALE_DATA['PersonsSales_payment'];
	$preText = $SALES->get_packageDescrip($packageID);
	
	$psp_sql = "SELECT * FROM PersonsSalesPayments WHERE PersonsSales_PersonsSales_id='".$SALE_ID."' ORDER BY PaymentDate ASC";
	$psp_snd = $DB->get_multi_result($psp_sql);
	foreach($psp_snd as $payment):
		$payments[] = array(
			'payDate'	=>	date("m/d/Y", $payment['PaymentDate']),
			'payAmt'	=>	$payment['PaymentAmount'],
			'payType'	=>	$payment['PayMethod'],
			'payREC'	=>	$payment['PayStatus'],
			'payDWN'	=>	$payment['isDownPayment']
		);
	endforeach;
	$maxCommission = $SALE_DATA['PersonsSales_MaxCommission'];
	$comPoolRate = @($totalPrice / $maxCommission);
	$salesText = $SALE_DATA['PersonsSales_SalesText'];
	
	$psc_sql = "SELECT * FROM PersonsSalesCommissions WHERE PersonsSales_PersonsSales_id='".$SALE_ID."'";
	$psc_snd = $DB->get_multi_result($psc_sql);
	$comRunning[] = 0.00;
	foreach($psc_snd as $commission):
		$comPerc = @round(($commission['CommissionAMT'] / $maxCommission) * 100);
		$comms[] = array(
			'comUserID'	=>	$commission['Users_user_id'],
			'comAMT'	=>	$commission['CommissionAMT'],
			'comPerc'	=>	$comPerc
		);
		$comRunning[] = $commission['CommissionAMT'];
	endforeach;	
	$comBalance = $maxCommission - array_sum($comRunning);
	
} else {
	$showPath = false;
	$dateCreated = date("m/d/Y");
	$saleOffice = array($P_DATA['Offices_id']);
	$packageID = 0;
	$basePrice = '';
	$taxesPrice = '';
	$totalPrice = '';
	$preText = '&nbsp;';
	$payments[] = array(
		'payDate'	=>	date("m/d/Y"),
		'payAmt'	=>	0.00,
		'payType'	=>	'',
		'payREC'	=>	0,
		'payDWN'	=>	0
	);
	$maxCommission = 0.00;
	$comPoolRate = 10;
	$salesText = '';
	$comms[] = array(
		'comUserID'	=>	$_SESSION['system_user_id'],
		'comAMT'	=>	0.00,
		'comPerc'	=>	0
	);
	$comBalance = 0.00;
}
?>
<div class="m-content">

<form id="salesForm" action="javascript:;" method="post" enctype="multipart/form-data">
<?php if($showPath): ?>
<div class="m-portlet ">
    <div class="m-portlet__body  m-portlet__body--no-padding">        
        <div class="row m-row--no-padding m-row--col-separator-xl">
            <div class="col-md-4 col-lg-4 col-xl-4">
                <!--begin::Total Profit-->
                <div class="m-widget24">
                    <div class="m-widget24__item">
                        <h4 class="m-widget24__title">
                            Step 1
                        </h4>
                        <br>
                        <span class="m-widget24__desc">
                            Select Package and Price
                        </span>
                        <span class="m-widget24__stats m--font-danger" id="status_step_1">
                            <i class="fa fa-remove" style="font-size:1.8em;"></i>
                        </span>                        
                    </div>
                    <div class="m--space-20"></div>
                </div>
                <!--end::Total Profit-->
            </div>
            <div class="col-md-4 col-lg-4 col-xl-4">
                <!--begin::New Feedbacks-->
                <div class="m-widget24">
                    <div class="m-widget24__item">
                        <h4 class="m-widget24__title">
                            Step 2
                        </h4>
                        <br>
                        <span class="m-widget24__desc">
                            Enter Payment Info
                        </span>
                        <span class="m-widget24__stats m--font-danger" id="status_step_2">
                            <i class="fa fa-remove" style="font-size:1.8em;"></i>
                        </span>                        
                    </div>
                    <div class="m--space-20"></div>
                </div>
                <!--end::New Feedbacks-->
            </div>
            <div class="col-md-4 col-lg-4 col-xl-4">
                <!--begin::New Orders-->
                <div class="m-widget24">
                    <div class="m-widget24__item">
                        <h4 class="m-widget24__title">
                            Step 3
                        </h4>
                        <br>
                        <span class="m-widget24__desc">
                            Enter Commissions
                        </span>
                        <span class="m-widget24__stats m--font-danger" id="status_step_3">
                            <i class="fa fa-remove" style="font-size:1.8em;"></i>
                        </span>                        
                    </div>
                    <div class="m--space-20"></div>
                </div>
                <!--end::New Orders-->
            </div>            
        </div>
    </div>
</div>
<?php endif; ?>

<!-- PACKAGE SELECT FORM -->
<div class="m-portlet" id="step_1">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-tool-1"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    Package Selection
                </h3>
            </div>
        </div>
    </div>

<div class="m-portlet__body">
    <div class="form-group m-form__group row">
        <label class="col-lg-2 col-form-label">
            Client/Member:
        </label>
        <div class="col-lg-4">
            <input type="text" class="form-control m-input m-input--solid" id="FullName" value="<?php echo $P_DATA['FirstName']?> <?php echo $P_DATA['LastName']?>" readonly="readonly" />
            <input type="hidden" name="Person_id" id="Person_id" value="<?php echo $PERSON_ID?>" />
            <input type="hidden" name="Sale_id" id="Sale_id" value="<?php echo $SALE_ID?>" />
        </div>
        <label class="col-lg-2 col-form-label">
            Sale Date:
        </label>
        <div class="col-lg-4">
            <div class="m-input-icon m-input-icon--right">
                <input type="text" class="form-control m-input" name="PersonsSales_dateCreated" id="PersonsSales_dateCreated" value="<?php echo $dateCreated?>" />
                <span class="m-input-icon__icon m-input-icon__icon--right">
                    <span>
                        <i class="la la-calendar-o"></i>
                    </span>
                </span>
            </div>
        </div>
    </div>
    <div class="form-group m-form__group row">
        <label class="col-lg-2 col-form-label">
            Location:
        </label>
        <div class="col-lg-4">
            <select class="form-control m-bootstrap-select m_selectpicker" id="Offices_Offices_id" name="Offices_Offices_id">
				<?php echo $RECORD->options_officeSelect($saleOffice)?>					
			</select> 
            <span class="m-form__help">
                where sale is executed
            </span>           
        </div>
        <label class="col-lg-2 col-form-label">
            <div id="packageLoader" class="m-loader m-loader--brand" style="width:30px; display:inline-block; float:right; margin-top:10px; display:none;"></div>
            Package:
        </label>
        <div class="col-lg-4">
            <select class="form-control m-bootstrap-select m_selectpicker" id="PersonsSales_packageID" name="PersonsSales_packageID">
				<?php echo $SALES->options_packages($packageID)?>					
			</select> 
            <span class="m-form__help">
                base package subscribing
            </span>
        </div>
    </div>
    
    <div class="form-group m-form__group row">
        <label class="col-lg-2 col-form-label">
            Contract:
        </label>
        <div class="col-lg-4">
            <select class="form-control m-bootstrap-select m_selectpicker" id="PersonsSales_ContractID" name="PersonsSales_ContractID">
				<option value="0"><em>None</em></option>
				<?php
                $c_sql = "SELECT * FROM PersonsContract WHERE Person_id='".$PERSON_ID."' ORDER BY Contract_dateEntered DESC";	
                $c_snd = $DB->get_multi_result($c_sql);	
                if(!isset($c_snd['empty_result'])):
                    foreach($c_snd as $c_dta):
                        ?><option value="<?php echo $c_dta['Contract_id']?>" <?php echo (($c_dta['Contract_id'] == $SALE_DATA['PersonsSales_ContractID'])? 'selected':'')?>>issued <?php echo date("m/d/y", $c_dta['Contract_dateEntered'])?> for $<?php echo $c_dta['Contract_RetainerFee']?></option><?php										
                    endforeach;
                endif;														
                ?>				
			</select> 
            <span class="m-form__help">
                what contract is this sale connected to.
            </span>           
        </div>
    </div>
    
    <hr />
    <div class="row">
    	<div class="col-lg-6">
        	<div class="alert alert-info" id="pacDESC"><?php echo $preText?></div>
		</div>
        <div class="col-lg-6">
            <div class="form-group m-form__group row">
                <label class="col-lg-4 col-form-label">                    
                    Base Price:
                </label>
                <div class="col-lg-8">
                    <div class="input-group">
                    	<span class="input-group-addon"><i class="fa fa-usd"></i></span>
	                    <input type="text" name="PersonsSales_basePrice" id="PersonsSales_basePrice" class="form-control m-input" value="<?php echo $basePrice?>" onblur="calculateTotal()" />                           
						<span class="input-group-btn">
                        	<button type="button" onclick="calculateTotal()" class="btn btn-sm btn-secondary"><i class="fa fa-calculator"></i></button>
                        </span>
                    </div>                      
                </div>
            </div>
            <div class="form-group m-form__group row">        
                <label class="col-lg-4 col-form-label">
                    Taxes:
                </label>
                <div class="col-lg-8">
                	<div class="input-group">
                    	<span class="input-group-addon"><i class="fa fa-usd"></i></span>
	                    <input type="text" name="PersonsSales_taxes" id="PersonsSales_taxes" value="<?php echo $taxesPrice?>" class="form-control m-input" />
                        <span class="input-group-addon">0.0</span>
					</div>
                </div>
            </div>
            <div class="form-group m-form__group row">        
                <label class="col-lg-4 col-form-label">
                    TOTAL:
                </label>
                <div class="col-lg-8">
                	<div class="input-group">
                    	<span class="input-group-addon"><i class="fa fa-usd"></i></span>
	                    <input type="text" name="PersonsSales_payment" id="PersonsSales_payment" value="<?php echo $totalPrice?>" class="form-control m-input" />
					</div>
                </div>
            </div>
		</div>
	</div>
</div> 
	<!--   
    <div class="m-portlet__foot">
        <div class="row align-items-center">
            <div class="col-lg-12 m--align-right">
                <button type="button" class="btn btn-brand" onclick="goToStep(2)">
                    Next <i class="fa fa-arrow-right"></i>
                </button>               
            </div>
        </div>
    </div>
    -->
</div>
<div class="m--space-20"></div>

<!-- PAYMENT SELECT FORM -->
<div class="m-portlet" id="step_2" style="display:none;">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-coins"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    Payment Information
                </h3>
            </div>
        </div>
    </div>
    <div class="m-portlet__body">
    
<div class="m-form__seperator m-form__seperator--dashed  m-form__seperator--space m--margin-bottom-40"></div>
    <div id="m_repeater_1">
        <div class="form-group  m-form__group row" id="m_repeater_1">
            <div data-repeater-list="" class="col-lg-12">
                
				<?php foreach($payments as $pay): ?>                
                <div data-repeater-item class="form-group m-form__group row align-items-center">
                    <div class="col-md-3">
                        <div class="m-form__group m-form__group--inline">
                            <div class="m-form__label">
                                <label>Amount:</label>
                            </div>
                            <div class="m-form__control">
                                <input type="text" class="form-control m-input PaymentAmount" name="PaymentAmount" value="<?php echo $pay['payAmt']?>">
                            </div>
                        </div>
                        <div class="d-md-none m--margin-bottom-10"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="m-form__group m-form__group--inline">
                            <div class="m-form__label">
                                <label class="m-label m-label--single">Date:</label>
                            </div>
                            <div class="m-form__control">
                            	<div class="input-group">
	                                <input type="text" class="form-control m-input input-date-field PaymentDate" name="PaymentDate" value="<?php echo $pay['payDate']?>">
                                    <span class="input-group-addon"><i class="la la-calendar-o"></i></span>
								</div>
                            </div>
                        </div>
                        <div class="d-md-none m--margin-bottom-10"></div>
                    </div>
                    <div class="col-md-3">
                        <div class="m-form__group m-form__group--inline">
                            <div class="m-form__label">
                                <label class="m-label m-label--single">Type:</label>
                            </div>
                            <div class="m-form__control">                                
                                <select class="form-control m-bootstrap-select p_selectpicker PayMethod" name="PayMethod">
									<option value=""></option>
                                    <option value="CHECK" <?php echo (($pay['payType'] == 'CHECK')? 'selected':'')?>>Check</option>
                                    <option value="CC" <?php echo (($pay['payType'] == 'CC')? 'selected':'')?>>Credit Card</option>
                                    <option value="WIRE" <?php echo (($pay['payType'] == 'WIRE')? 'selected':'')?>>Wire Transfer</option>
                                    <option value="CASH" <?php echo (($pay['payType'] == 'CASH')? 'selected':'')?>>Cash</option>                                    					
								</select>
                            </div>
                        </div>
                        <div class="d-md-none m--margin-bottom-10"></div>
                    </div>
                    <div class="col-md-2">
                        <div class="m-radio-inline">
                            <label class="m-checkbox m-checkbox--state-success">
                                <input type="checkbox" name="PayReceived" class="PayReceived" value="1" <?php echo (($pay['payREC'] == 1)? 'checked':'')?>>
                                Received
                                <span></span>
                            </label>
                            <label class="m-checkbox m-checkbox--state-success">
                                <input type="checkbox" name="DownPayment" class="DownPayment" value="1" <?php echo (($pay['payDWN'] == 1)? 'checked':'')?>>
                                Down Payment
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill">
                            <span>
                                <i class="la la-trash-o"></i>
                                <span>&nbsp;</span>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="m-form__group form-group row">
            <label class="col-lg-2 col-form-label"></label>
            <div class="col-lg-4">
                <div data-repeater-create="" class="btn btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill m-btn--wide">
                    <span>
                        <i class="la la-plus"></i>
                        <span>
                            Add
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>    
        
    </div>
    <div class="m-portlet__foot">
        <div class="row align-items-center">
            <div class="col-lg-6 m--align-left">
                <button type="button" class="btn btn-brand" onclick="goToStep(1)">
                    <i class="fa fa-arrow-left"></i> Previous 
                </button>               
            </div>
            <div class="col-lg-6 m--align-right">
                <button type="button" class="btn btn-brand" onclick="goToStep(3)">
                    Next <i class="fa fa-arrow-right"></i>
                </button>               
            </div>
        </div>
    </div>
</div>
<div class="m--space-20"></div>

<!-- COMMISSIONS SELECT FORM -->
<div class="m-portlet" id="step_3">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-book"></i>
                </span>
                <h3 class="m-portlet__head-text">
                	Commission(s)
                </h3>
            </div>
        </div>
    </div>
    <div class="m-portlet__body">
    
<div class="row">
	<div class="col-lg-6">
    
    	<div class="form-group m-form__group row">
            <label class="col-lg-4 col-form-label">
                Base Commisisons:
            </label>
            <div class="col-lg-7">
                <select class="form-control m-bootstrap-select m_selectpicker" id="SalesCommissionRate" name="SalesCommissionRate" onchange="calculateTotal()">
                    <?php
                    for ($i=0; $i<40; $i++) {
                        ?><option value="<?php echo $i?>" <?php echo (($i == $comPoolRate)? 'selected':'')?>><?php echo $i?> %</option><?php	
                    }
                    ?>
                    </select>					
                </select> 
                <span class="m-form__help">
                    % of total allowed for commisions.
                </span>
            </div>
        </div>
        <div class="form-group m-form__group row">
            <label class="col-lg-4 col-form-label">
                This Sale:
            </label>
            <div class="col-lg-7">
            	<div class="input-group">
	                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                    <input type="text" id="commSaleTotal" class="form-control m-input m-input--solid" readonly value="<?php echo $maxCommission?>" /> 
				</div>
            </div>
	    </div>
        
        <div class="form-group m-form__group row">
            <label class="col-lg-4 col-form-label">
                Commission Balance:
            </label>
            <div class="col-lg-7">
            	<div class="input-group">
	                <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                    <input type="text" id="commSaleBalance" class="form-control m-input m-input--solid" readonly value="<?php echo $comBalance?>" /> 
				</div>
            </div>
	    </div>

	</div>
    <div class="col-lg-6">
    	<div class="form-group m-form__group">
            <label for="exampleTextarea">Free Text Area</label>
            <textarea class="form-control m-input" id="PersonsSales_SalesText" name="PersonsSales_SalesText" rows="5"><?php echo $salesText?></textarea>
        </div>
    </div>
</div>
<div class="row">
	<div class="col-lg-12">
    	
        <div id="m_repeater_2">
            <div class="form-group  m-form__group row" id="m_repeater_2">
                <div data-repeater-list="" class="col-lg-12">
                    
					<?php foreach($comms as $comm): ?>
                    <div data-repeater-item class="form-group m-form__group row align-items-center">
                        <div class="col-md-5">
                            <div class="m-form__group m-form__group--inline">
                                <select class="form-control user-dropdown salesAssociate" name="salesAssociate" style="width:100%;">
                                    <?php echo $RECORD->options_userSelect(array($comm['comUserID']))?>
                                </select>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                    <input type="number" name="associateDollars" value="<?php echo $comm['comAMT']?>" class="form-control m-input associateDollars m-input--solid associateDollars" readonly />                     
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                    <input type="number" name="associatePerc"  value="<?php echo $comm['comPerc']?>" class="form-control m-input associatePerc" /> 
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-1">
                            <div data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill">
                                <span>
                                    <i class="la la-trash-o"></i>
                                    <span>&nbsp;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="m-form__group form-group row">
                <label class="col-lg-2 col-form-label"></label>
                <div class="col-lg-4">
                    <div data-repeater-create="" class="btn btn btn-sm btn-brand m-btn m-btn--icon m-btn--pill m-btn--wide">
                        <span>
                            <i class="la la-plus"></i>
                            <span>
                                Add
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>        
        
    </div>
    <div class="m-portlet__foot">
        <div class="row align-items-center">
            <div class="col-lg-6 m--align-left">
                <!--
                <button type="button" class="btn btn-brand" onclick="goToStep(2)">
                    <i class="fa fa-arrow-left"></i> Previous 
                </button>
                -->               
            </div>
            <div class="col-lg-6 m--align-right">
                <button type="button" class="btn btn-success" onclick="goToStep(4)">
                    Review Sale Info <i class="fa fa-arrow-right"></i>
                </button>               
            </div>
        </div>
    </div>
</div>

<!-- REVIEW FORM -->
<div class="m-portlet" id="step_4" style="display:none;">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon">
                    <i class="flaticon-book"></i>
                </span>
                <h3 class="m-portlet__head-text">
                	Sale Review
                </h3>
            </div>
        </div>
    </div>
    <div class="m-portlet__body">
    	
        <div class="row">
        	<div class="col-lg-3">
            	<div>Client: <strong id="display_recipName"><?php echo $P_DATA['FirstName']?> <?php echo $P_DATA['LastName']?></strong></div>
                <div class="m--margin-bottom-10"></div>
                
                <div>Office: <strong id="display_officeName">NATIONAL</strong></div>
                <div class="m--margin-bottom-10"></div>
                
                <div>Sale Date: <strong id="display_saleDate">{SALE DATE}</strong></div>
                <div class="m--margin-bottom-10"></div>
                
                <div>Contract: <strong id="display_contractLink">[CONTRACT]</strong></div>
                <div class="m--margin-bottom-10"></div>
                
                <div>Package: <strong id="display_packageName">{Package}</strong></div>
                <div class="m--margin-bottom-10"></div>
			</div>
            <div class="col-lg-3">                
                <div>Base Price: <strong id="display_basePrice">{SALE DATE}</strong></div>
                <div class="m--margin-bottom-10"></div>
                
                <div>Taxes: <strong id="display_baseTaxes">{Package}</strong></div>
                <div class="m--margin-bottom-10"></div>
                
                <div>Total: <strong id="display_baseTotal">{Package}</strong></div>
                <div class="m--margin-bottom-10"></div>
			</div>
            <div class="col-lg-6">
<!--
<h6>Payment(s)</h6>            
<div class="m-list-timeline" id="payList"></div>
-->

<h6 style="margin-top:30px;">Commissions</h6>
<div class="m-list-timeline" id="comList"></div> 

<h6 style="margin-top:30px;">Sale Notes</h6>
<div class="m-demo__preview" id="displaySaleNotes"></div>
                
			</div>            
		</div>                            
    

    </div>
    <div class="m-portlet__foot">
        <div class="row align-items-center">
            <div class="col-lg-6 m--align-left">
                <button type="button" class="btn btn-brand" onclick="goToStep(3)">
                    <i class="fa fa-arrow-left"></i> Cancel 
                </button>               
            </div>
            <div class="col-lg-6 m--align-right">
                <button type="button" class="btn btn-success" onclick="generateSale()">
                    Write Sale to Database <i class="fa fa-check"></i>
                </button>               
            </div>
        </div>
    </div>
</div>

<div id="bonusForm"></div>
<?php echo $SESSION->renderToken()?>
</form>

<div class="row" style="margin-top:50px; display:none;" id="completedPanel">
	<div class="col-lg-3">&nbsp;</div>
    <div class="col-lg-6">
    
<div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-success alert-dismissible fade show" role="alert" id="saleCompleteAlert">
	<div class="m-alert__icon">
		<i class="flaticon-exclamation-1"></i>
		<span></span>
	</div>
	<div class="m-alert__text">
		<strong>Sale Record [Created:Updated]</strong><br />A sale record for [PersonNAME] has been created.<br /><a href="#">Click here to view record</a>
	</div>
</div>

	</div>
    <div class="col-lg-3">&nbsp;</div>
</div>    


</div>
<script>
$(document).ready(function(e) {
	$(".m_selectpicker").selectpicker();
	$("#PersonsSales_dateCreated").datepicker({
		todayHighlight: !0,
		orientation: "bottom left",
		templates: {
			leftArrow: '<i class="la la-angle-left"></i>',
			rightArrow: '<i class="la la-angle-right"></i>'
		}
	});
	$(document).on('change', '#PersonsSales_packageID', function() {
		$('#packageLoader').show();
		$('#pacDESC').html('<div class="m-loader" style="width: 30px; display: inline-block;"></div> Loading Package Info...');			
		$.post('/ajax/ajax.sales.php?action=getPackageData', {
			pacID:	$(this).val() 	
		}, function(data) {
			$('#packageLoader').hide();
			$('#PersonsSales_basePrice').val(data.SellableEntities_price);
			$('#pacDESC').html(data.SellableEntities_description);			
			calculateTotal();
		}, "json");
	});
	
	$("#m_repeater_1").repeater({
		initEmpty: !1,
		defaultValues: {
			"PaymentDate": "<?php echo date("m/d/Y")?>"
		},
		show: function() {
			$(this).slideDown();
			$(".p_selectpicker").selectpicker();
			$(".input-date-field").datepicker({
				todayHighlight: !0,
				format: 'mm/dd/yyyy',
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
		},
		hide: function(e) {
			$(this).slideUp(e)
		},
		ready: function (setIndexes) {
			$(".p_selectpicker").selectpicker();
			$(".input-date-field").datepicker({
				todayHighlight: !0,
				format: 'mm/dd/yyyy',
				orientation: "bottom left",
				templates: {
					leftArrow: '<i class="la la-angle-left"></i>',
					rightArrow: '<i class="la la-angle-right"></i>'
				}
			});
		},
		isFirstItemUndeletable: true
	});           
    autosize($('#PersonsSales_SalesText'));
	
	
	$("#m_repeater_2").repeater({
		initEmpty: !1,
		defaultValues: {
			"text-input": "foo"
		},
		show: function() {
			$(this).slideDown();
			$('.user-dropdown').select2({ theme: "classic" });
			resetCommissions();
		},
		hide: function(e) {
			$(this).slideUp(e)
		},
		ready: function (setIndexes) {
			$('.user-dropdown').select2({ theme: "classic" });
			resetCommissions();			
		},
		isFirstItemUndeletable: false
	});
	
	$(document).on('blur', '.associatePerc', function() {
		var percVal = $(this).val();
		//console.log(percVal);
		var comSalesTotal = $('#commSaleTotal').val();
		var myCommission = comSalesTotal * (percVal / 100);
		//alert(myCommission);
		recalculateCommissions();		
	});
});
function goToStep(step) {
	var priceBase 	= $('#PersonsSales_basePrice').val();
	var priceTax	= $('#PersonsSales_taxes').val();
	var priceTotal	= $('#PersonsSales_payment').val();
	if(step == 2) {
		if ((priceBase = '') || (priceTax == '') || (priceTotal == '')) {
			alert('You must select a package and enter in a price before proceeding to the next step.');	
		} else {
			if($('#step_1').is(':visible')) {
				$('#step_1').fadeOut('fast', function() {
					$('#step_2').fadeIn('fast');
					$('#status_step_1').addClass('m--font-success');
					$('#status_step_1').removeClass('m--font-danger');
					$('#status_step_1').html('<i class="fa fa-check" style="font-size:1.8em;"></i>');
				});
			}			
			if($('#step_3').is(':visible')) {
				$('#step_3').fadeOut('fast', function() {
					$('#step_2').fadeIn('fast');
					$('#status_step_2').removeClass('m--font-success');
					$('#status_step_2').addClass('m--font-danger');
					$('#status_step_2').html('<i class="fa fa-remove" style="font-size:1.8em;"></i>');
				});
			}
		}
	} else if(step == 1) {
		$('#step_2').fadeOut('fast', function() {
			$('#step_1').fadeIn('fast');
			$('#status_step_1').removeClass('m--font-success');
			$('#status_step_1').addClass('m--font-danger');
			$('#status_step_1').html('<i class="fa fa-remove" style="font-size:1.8em;"></i>');
		});	
	} else if(step == 3) {
		if($('#step_2').is(':visible')) {
			var error = 0;
			$('.PayMethod').each(function() {
				console.log();			
				if($(this).selectpicker('val') == '') {
					error = 1;
				}
			});
			if(error == 1) {
				alert('You must select a type of payment for each entry');
			} else {
				$('#step_2').fadeOut('fast', function() {
					$('#step_3').fadeIn('fast');
					$('#status_step_2').addClass('m--font-success');
					$('#status_step_2').removeClass('m--font-danger');
					$('#status_step_2').html('<i class="fa fa-check" style="font-size:1.8em;"></i>');
				});	
			}
		}
		if($('#step_4').is(':visible')) {
			$('#step_4').fadeOut('fast', function() {
				$('#step_1').show();
				$('#step_3').fadeIn('fast');
				$('#status_step_3').removeClass('m--font-success');
				$('#status_step_3').addClass('m--font-danger');
				$('#status_step_3').html('<i class="fa fa-remove" style="font-size:1.8em;"></i>');
			});
		}
	} else if(step == 4) {
		$('#step_1').hide();
		$('#step_3').fadeOut('fast', function() {
			$('#step_4').fadeIn('fast');
			
			$('#status_step_3').removeClass('m--font-danger');
			$('#status_step_3').addClass('m--font-success');
			$('#status_step_3').html('<i class="fa fa-check" style="font-size:1.8em;"></i>');

			var formData = $('#salesForm').serializeArray();
			var name = $('#FullName').val();
			var date = $('#PersonsSales_dateCreated').val();
			
			var basePrice = $('#PersonsSales_basePrice').val();
			var taxPrice = $('#PersonsSales_taxes').val();
			var totalPrice = $('#PersonsSales_payment').val();
			
			var saleText = $('#PersonsSales_SalesText').val();
			
			console.log(basePrice);
			$('#display_recipName').html(name);	
			$('#display_saleDate').html(date);
			$('#display_basePrice').html('$'+basePrice);
			$('#display_baseTaxes').html('$'+taxPrice);
			$('#display_baseTotal').html('$'+totalPrice);
			
			$('#displaySaleNotes').html('<p>'+saleText+'</p>');
			
			var payments_amt = Array();
			var index1 = 0;
			$('.PaymentAmount').each(function() {
				payments_amt[index1] = $(this).val();
				index1++;
			});
			
			var payments_date = Array();
			var index2 = 0;
			$('.PaymentDate').each(function() {
				payments_date[index2] = $(this).val();
				index2++;
			});
			
			var payments_type = Array();
			var paymentTemp = Array();
			var index3 = 0;
			$('.PayMethod').each(function() {
				var selectValue = $(this).selectpicker('val');
				//console.log(selectValue);
				if (typeof selectValue == 'string') {
					paymentTemp[0] = selectValue;
					payments_type[index3] = paymentTemp[0];
					index3++;
				}			 
			});
			//console.log(payments_type);
			//console.log(payments_amt);
			
			var payRec = Array();
			var index4 = 0;
			$('.PayReceived').each(function() {
				if($(this).is(':checked')) {	
					payRec[index4] = 1;
				} else {
					payRec[index4] = 0;
				}
				index4++;
			});
			//console.log(payRec);
			
			var payDown = Array();
			var index5 = 0;
			$('.DownPayment').each(function() {
				if($(this).is(':checked')) {	
					payDown[index5] = 1;
				} else {
					payDown[index5] = 0;
				}
				index5++;
			});
			//console.log(payDown);
			
			var salesAssoc = Array();
			var salesNames = Array();
			var index6 = 0;
			$('.salesAssociate').each(function() {
				salesAssoc[index6] = $(this).val();
				salesNames[index6] = $("option:selected", this).text();
				index6++;
			});
			//console.log(salesAssoc);
			//console.log(salesNames);
			
			var commDollars = Array();
			var index7 = 0;
			$('.associateDollars').each(function() {
				commDollars[index7] = $(this).val();	
				index7++;
			});
			//console.log(commDollars);
			
			var formElement = '';
			var payRow = '';
			for(l=0; l<payments_amt.length; l++) {
				payRow += '<div class="m-list-timeline__items">';
					payRow += '<div class="m-list-timeline__item">';
						payRow += '<span class="m-list-timeline__badge m-list-timeline__badge--success"></span>';
						payRow += '<span class="m-list-timeline__icon fa fa-usd"></span>';
						payRow += '<span class="m-list-timeline__text">';
							payRow += payments_amt[l]+' - '+payments_type[l];
							if(payRec[l] == 1) {
								payRow += '&nbsp;&nbsp;&nbsp;<i class="fa fa-check m--font-primary" data-toggle="m-tooltip" title="Payment Received"></i>';
							}
							if(payDown[l] == 1) {
								payRow += '&nbsp;&nbsp;&nbsp;<i class="fa fa-check m--font-success" data-toggle="m-tooltip" title="Down Payment"></i>';
							}
						payRow += '</span>';
						payRow += '<span class="m-list-timeline__time">'+payments_date[l]+'</span>';
					payRow += '</div>';
				payRow += '</div>';
				
				formElement += '<input type="hidden" name="payAMT[]" value="'+payments_amt[l]+'">';
				formElement += '<input type="hidden" name="payTYPE[]" value="'+payments_type[l]+'">';
				formElement += '<input type="hidden" name="payDATE[]" value="'+payments_date[l]+'">';
				if(payRec[l] == 1) {
					formElement += '<input type="hidden" name="payREC[]" value="1">';
				} else {
					formElement += '<input type="hidden" name="payREC[]" value="0">';
				}
				if(payDown[l] == 1) {
					formElement += '<input type="hidden" name="payDWN[]" value="1">';
				} else {
					formElement += '<input type="hidden" name="payDWN[]" value="0">';
				}
			}
			//console.log(payRow);
			
			var comRow = '';
			for(u=0; u<salesAssoc.length; u++) {
				comRow += '<div class="m-list-timeline__items">';
					comRow += '<div class="m-list-timeline__item">';
						comRow += '<span class="m-list-timeline__badge m-list-timeline__badge--brand"></span>';
						comRow += '<span class="m-list-timeline__icon fa fa-user"></span>';
						comRow += '<span class="m-list-timeline__text">'+salesNames[u]+'</span>';
						comRow += '<span class="m-list-timeline__time">$'+commDollars[u]+'</span>';
					comRow += '</div>';
				comRow += '</div>';	
				
				formElement += '<input type="hidden" name="commUser[]" value="'+salesAssoc[u]+'">';
				formElement += '<input type="hidden" name="commAmt[]" value="'+commDollars[u]+'">';
			}
			
			$('#payList').html(payRow);
			$('#comList').html(comRow);
			
			$('#display_officeName').html('<div class="m-loader" style="width: 30px; display: inline-block;"></div>');
			$('#display_packageName').html('<div class="m-loader" style="width: 30px; display: inline-block;"></div>');
			
			$('#bonusForm').html(formElement);
			$.post('/ajax/ajax.sales.php?action=preview', formData, function(data) {
				$('#display_officeName').html(data.office);
				$('#display_packageName').html(data.package);
				$('#display_contractLink').html(data.contract);				
				$('[data-toggle="m-tooltip"]').tooltip();
			}, "json");
		});
		
	}
	
}
function generateSale() {
	var formData = $('#salesForm').serializeArray();
	$.post('/ajax/ajax.sales.php?action=writeSale', formData, function(data) {
		console.log(data);
		if(data.update) {
			var strongString = 'Sale Record Updated';
			var pString = 'A sale record for '+$('#FullName').val()+' has been updated.';
			var pLink = '/profile/'+data.record;
		} else {
			var strongString = 'Sale Record Created';
			var pString = 'A sale record for '+$('#FullName').val()+' has been created.';
			var pLink = '/profile/'+data.record;
			
		}
		var finalAlertString = '<strong>'+strongString+'</strong><br />'+pString+'<br /><a href="'+pLink+'">Click here to view record</a>';
		$('#saleCompleteAlert .m-alert__text').html(finalAlertString);
		$('#step_4').fadeOut('fast', function() {
			$('#completedPanel').fadeIn('fast');
		});
	}, "json");
	
	
}
function calculateTotal() {
	var basePrice = eval($('#PersonsSales_basePrice').val());
	var taxBase = .0;
	var taxTotal = basePrice * taxBase;
	var grandTotal = basePrice + taxTotal;
	$('#PersonsSales_taxes').val(taxTotal.toFixed(2));
	$('#PersonsSales_payment').val(grandTotal.toFixed(2));
	$('input[name="[0][PaymentAmount]"]').val(grandTotal.toFixed(2));
	$('input[name="[0][PaymentDate]"]').val(moment().format('MM/DD/YYYY'));
	var comPerc = $('#SalesCommissionRate').val();
	var comTotal = grandTotal * (comPerc / 100);
	$('#commSaleTotal').val(comTotal.toFixed(2));
	resetCommissions();
	calculateCommissions();
}
function resetCommissions() {
	var comSalesTotal = $('#commSaleTotal').val();
	var associates = $('.salesAssociate').length;
	var evenPerc = Math.round((100 / associates));
	var evenMoney = comSalesTotal * (evenPerc / 100);
	$('.associatePerc').each(function() {
		$(this).val(evenPerc);	
	});
	$('.associateDollars').each(function() {
		$(this).val(evenMoney.toFixed(2));					
	});
}
function recalculateCommissions() {
	var index = 0;
	var perc = Array;
	$('.associatePerc').each(function() {
		perc[index] = $(this).val();
		index++;
	});
	index2 = 0;
	var comSalesTotal = $('#commSaleTotal').val();
	$('.associateDollars').each(function() {
		var evenMoney = comSalesTotal * (perc[index2] / 100);		
		$(this).val(evenMoney.toFixed(2));
		index2++;				
	});
	calculateCommissions();
}
function calculateCommissions() {
	comSalesTotal = $('#commSaleTotal').val();
	//$('input[name="[0][asscoiateDollars]"]').val(comSalesTotal);
	//$('#commSaleBalance').val(comSalesTotal.toFixed(2));
	var dollarTotal = 0;
	var placeholder = 0;
	$('.associateDollars').each(function() {
		var tempVal = $(this).val();
		placeholder = dollarTotal + eval(tempVal);
		dollarTotal = placeholder;
	});
	//alert(dollarTotal);
	var dollarBalance = (comSalesTotal - dollarTotal);
	$('#commSaleBalance').val(dollarBalance.toFixed(2));
}
</script>

