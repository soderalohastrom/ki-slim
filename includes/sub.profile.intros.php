<?php
include_once("class.matching.php");
$MATCHING = new Matching($DB, $RECORD);
$defaultFilters = array(0, 2, 3, 4, 5, 6, 99, 101, 102);
?>
<div class="row">
    <div class="col-12">
	<?php $RADDRESS->render_addressStackWithTopMatches($PERSON_ID);?> 
    </div>
</div>    
<div class="m-form__group form-group row">
    <div class="col-10">    	
        <div class="row">
        	<div class="col-3">
                SHOWING: <span id="introDisplayNumber"></span>
            </div>
            <div class="col-3">
                TOTAL:	<span id="introTotalNumber"></span>
            </div>
            <div class="col-6">
            	<form id="searchForm" action="javascript:searchDates();">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon" id="basic-addon2">
                        <i class="la la-search" aria-hidden="true"></i>
                    </span>
                    <input type="text" class="form-control m-input" id="datesSearchText" placeholder="Search" aria-describedby="basic-addon2">
                </div>
                </form>
                
            </div>
		</div>
        <div class="row">
        	<div class="col-12" id="introStats">
            
            </div>
		</div>                   
    </div>
    <div class="col-2">
    	
        <div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="click" aria-expanded="true" data-dropdown-persistent="true">
            <a href="#" class="m-portlet__nav-link m-dropdown__toggle btn btn-secondary m-btn m-btn--icon m-btn--pill">
                <i class="la la-ellipsis-v"></i>
            </a>
            <div class="m-dropdown__wrapper">
            	
                <div class="m-dropdown__inner">
					<div class="m-dropdown__body">
						<div class="m-dropdown__content">
							<?php echo $MATCHING->render_dateStatus_checkboxes($defaultFilters);?>
                            <div style="margin-top:10px; text-align:right;">
                                <button type="button" class="btn m-btn--pill btn-outline-brand btn-sm" onclick="fetchIntros()">
                                    Update View
                                </button>
                            </div>
						</div>
					</div>
				</div>
                
            </div>
        </div>
        
    </div>
</div>
<div id="introListResults"></div>

<div class="modal fade" id="IntroMakeModal" role="dialog" aria-labelledby="IntroMakeModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="IntroMakeModalLabel">Create Intro</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="m-form" id="NewIntroForm">
                <div class="row">
                	<div class="col-4">Participant #1</div>
                    <div class="col-8">
                    	<strong><?php echo $PDATA['FirstName']?> <?php echo $PDATA['LastName']?></strong>
                    </div>                
                </div>
                <hr />  
                <div class="row">
                	<div class="col-4">Participant #2</div>
                    <div class="col-8">
                    	<div class="m-typeahead"> 
		                    <input type="text" class="form-control m-input" id="bioSearch" name="bioSearch" aria-describedby="emailHelp" placeholder="Search..."  style="width:100%;">
        	            </div>
                        <span class="m-form__help">First &amp; Last Name, Email or ID</span>
					</div>                        
                </div>
				<input type="hidden" name="bio_id_1" id="bio_id_1" value="" />
                <input type="hidden" name="bio_id_2" id="bio_id_2" value="" />

    			</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="createDateRecord()" data-toggle="m-tooltip" title="Clicking this will create an intro record with these two persons."><i class="la la-heart"></i> Create Intro Record</button>
			</div>
		</div>
	</div>
</div>

<script>
var bioSearchObject = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: '../data/films/post_1960.json',
  remote: {
    url: '/ajax/bioSearch.php?action=query&q=%QUERY',
    wildcard: '%QUERY'
  }
});

$(document).ready(function(e) {
    $("#introFilters").select2({
		placeholder: "Select a intro stage"
    });
	$('#introFilters').on('change.select2', function (e) {
  		alert('change to select');
		fetchIntros();
	});
	$('#introFilters').on('select2:unselect', function (e) {
  		//alert('change to select');
		fetchIntros();
	});
	fetchIntros();
	
    $('#bioSearch').typeahead(null, {
		hint: true,
		highlight: true,
		minLength: 4,
		name: 'bio-search',
		display: 'name',
		source: bioSearchObject
	});
	$('#bioSearch').bind('typeahead:select', function(ev, suggestion) {
		console.log(ev)
		console.log(suggestion);
		$('#bio_id_2').val(suggestion.id);
	});
	$('#bioLoadModal').on('hidden.bs.modal', function (e) {
		$('#bio_id_2').val('');
		$('#bioSearch').val('');
	});
});
function createDateRecord() {
	if ($('#bio_id_2').val() != '') {
		var person1 = '<?php echo $PERSON_ID?>';
		var person2 = $('#bio_id_2').val();	
		createDate(person1, person2, '101');
		$('#IntroMakeModal').modal('hide');	
	} else {
		alert('You must select a person before creating an intro.');
	}
}
function searchDates() {
	var q = $('#datesSearchText').val();	
	var introStati = new Array();
	var index = 0;
	$('.introStatus').each(function() {
		if($(this).is(':checked')) {
			introStati[index] = $(this).val();
			index++;
		}
	});
	$('#introListResults').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Data History...');
	$.post('/ajax/intros.php?action=dateTable', {
		pid: '<?php echo $PERSON_ID?>',
		filter: introStati,
		query:	q
	}, function(data) {
		$('#introListResults').html(data.html);
		$('#introDisplayNumber').html(data.display);
		$('#introTotalNumber').html(data.total);
		$('#introStats').html(data.stats);
		$("#intro-list").tablesorter();		
	}, "json");
	return false;	
}

function fetchIntros() {
	var introStati = new Array();
	var index = 0;
	$('.introStatus').each(function() {
		if($(this).is(':checked')) {
			introStati[index] = $(this).val();
			index++;
		}
	});
	$('#introListResults').html('<div class="m-loader m-loader--brand m-loader--lg" style="width:30px; display: inline-block;"></div> Loading Data History...');
	$.post('/ajax/intros.php?action=dateTable', {
		pid: '<?php echo $PERSON_ID?>',
		filter: introStati,
		query:	''
	}, function(data) {
		$('#introListResults').html(data.html);
		$('#introDisplayNumber').html(data.display);
		$('#introTotalNumber').html(data.total);
		$('#introStats').html(data.stats);
		$("#intro-list").tablesorter();
		mApp.init();		
	}, "json");	
}
</script>
