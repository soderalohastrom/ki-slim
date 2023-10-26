<?php
$QUESTION_ID = $pageParamaters['params'][0];
$TAB = $pageParamaters['params'][1];

$q_sql = "
SELECT
	*
FROM
	PrefQuestions
	INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=PrefQuestions.QuestionTypes_id
WHERE
	1
AND
	PrefQuestions.PrefQuestion_id='".$QUESTION_ID."'
	
";
$Q_DATA = $DB->get_single_result($q_sql);

if(($Q_DATA['QuestionTypes_name'] == 'SELECT') || ($Q_DATA['QuestionTypes_name'] == 'RADIO') || ($Q_DATA['QuestionTypes_name'] == 'CHECKBOX')):
	$qa_sql = "SELECT * FROM QuestionsAnswers WHERE Questions_id='".$Q_DATA['Questions_id']."' ORDER BY QuestionsAnswers_order ASC";
	$qa_snd = $DB->get_multi_result($qa_sql);
	if(isset($qa_snd['empty_result'])) {
		$a_array[] = '';		
	} else {
		foreach($qa_snd as $qa_dta):
		$a_array[] = $qa_dta['QuestionsAnswers_value'];
		endforeach;		
	}
else:
	$a_array = array();
endif;

if($QUESTION_ID == 0) {
	$a_array[] = '';	
}

$catSql = "SELECT * FROM QuestionsCategories ORDER BY QuestionsCategories_id ASC";
$catSnd = $DB->get_multi_result($catSql);
ob_start();
foreach($catSnd as $catDta):
	?><option value="<?php echo $catDta['QuestionsCategories_id']?>" <?php echo (($Q_DATA['QuestionsCategories_id'] == $catDta['QuestionsCategories_id'])? 'selected':'')?>><?php echo $catDta['QuestionsCategories_name']?></option><?php
endforeach;
$catSelect = ob_get_clean();

$typeSQL = "SELECT * FROM QuestionTypes ORDER BY QuestionTypes_id ASC";
$typeSnd = $DB->get_multi_result($typeSQL);
ob_start();
foreach($typeSnd as $typeDta):
	?><option value="<?php echo $typeDta['QuestionTypes_id']?>" <?php echo (($Q_DATA['QuestionTypes_id'] == $typeDta['QuestionTypes_id'])? 'selected':'')?>><?php echo $typeDta['QuestionTypes_name']?></option><?php
endforeach;
$typeSelect = ob_get_clean();

ob_start();
$qq_sql = "SELECT * FROM Questions WHERE QuestionTypes_id IN (3,4,5) AND Questions_active='1' ORDER BY Questions_text";
$qq_snd = $DB->get_multi_result($qq_sql);
foreach($qq_snd as $qq_dta):
	?><option value="<?php echo $qq_dta['Questions_id']?>" <?php echo (($Q_DATA['Questions_id'] == $qq_dta['Questions_id'])? 'selected':'')?>><?php echo $qq_dta['Questions_text']?></option><?php
endforeach;
$qSelect = ob_get_clean();

if($QUESTION_ID != 0) {
	$k_sql = "SELECT * FROM Questions WHERE Questions_id='".$Q_DATA['Questions_id']."'";
	$k_snd = $DB->get_single_result($k_sql);
}
?>
<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
<style>
.item-handle {
	cursor:move;
}
</style>
<div class="m-content">

<div class="m-portlet">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <span class="m-portlet__head-icon m--hide">
                    <i class="la la-gear"></i>
                </span>
                <h3 class="m-portlet__head-text">
                    Edit/Create Question
                    <small>preference questions</small>
                </h3>
            </div>
        </div>
    </div>
    <!--begin::Form-->
    <form class="m-form" id="question-form" name="qForm" action="javascript:submitQform();">
    	<input type="hidden" name="PrefQuestion_id" id="PrefQuestion_id" value="<?php echo $QUESTION_ID?>" />
        <input type="hidden" name="PrefQuestions_mappedMatchField" id="PrefQuestions_mappedMatchField" value="<?php echo $k_snd['MappedField']?>" />
        <input type="hidden" name="QuestionTypes_id" id="QuestionTypes_id" value="<?php echo $k_snd['QuestionTypes_id']?>" />
        <div class="m-portlet__body">
            <div class="m-form__section m-form__section--first">
                <div class="form-group m-form__group row">
                    <label class="col-lg-2 col-form-label">
                        Question:
                    </label>
                    <div class="col-lg-10">
                        <input type="text" name="PrefQuestion_text" id="PrefQuestion_text" class="form-control m-input" value="<?php echo $Q_DATA['PrefQuestion_text']?>" required="required">
                        <span class="m-form__help">
                            Please enter the full question text as it will appear in profile and in forms.
                        </span>
                    </div>
                </div>
                <div class="row">
                	<div class="col-lg-6">              
                        <div class="form-group m-form__group row">
                            <label class="col-lg-4 col-form-label">
                                Connected Profile Question:
                            </label>
                            <div class="col-lg-8">
                                <select name="Questions_id" id="Questions_id" class="form-control m-input">
                                    <?php echo $qSelect?>
                                </select>
                            </div>
                        </div>
                        <div class="m-form__group form-group row">
                            <label class="col-4 col-form-label">
                                Question Status
                            </label>
                            <div class="col-8">
                                <div class="m-radio-inline">
                                    <label class="m-radio">
                                        <input type="radio" name="PrefQuestion_active" value="1" <?php echo (($Q_DATA['PrefQuestion_active'] == 1)? 'checked':'')?>>
                                        Active
                                        <span></span>
                                    </label>
                                    <label class="m-radio">
                                        <input type="radio" name="PrefQuestion_active" value="0" <?php echo (($Q_DATA['PrefQuestion_active'] == 0)? 'checked':'')?>>
                                        Inactive
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>                
                        <div class="form-group m-form__group row">
                            <label class="col-lg-4 col-form-label">&nbsp;</label>
                            <div class="col-lg-8">
                                <div class="m-checkbox-inline">
                                    <label class="m-checkbox">
                                        <input type="checkbox" name="PrefQuestions_inPortal" id="PrefQuestions_inPortal" value="1" <?php echo (($Q_DATA['PrefQuestions_inPortal'] == 1)? 'checked':'')?>>
                                        Show in Client Portal
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
					</div>
                    <div class="col-lg-6">
                    	
                        <div class="row">
                        	<div class="col-lg-3">
                            	Options
                                <span class="m-form__help">To edit possible values, edit the coresponding profile question.</span>
							</div>
                            <div class="col-lg-9">                                
                                <div id="options-list">
                                    <ul class="list-group">
                                    <?php foreach($a_array as $option): ?>
                                    <li class="list-group-item"><?php echo $option?></li>
                                    <?php endforeach; ?>
                                    </ul>
                                </div>
							</div>
						</div>                                                            
                    
                    </div>                       
				</div>                                        
                
            </div>
        </div>
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions m-form__actions">
                <div class="row">
                    <div class="col-lg-6">
                    	<a href="/pmanager" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Questions</a>
                        <button type="submit" class="btn btn-success">Save/Update Question</button>
                        
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!--end::Form-->
</div>




</div>
<script>
$(document).ready(function(e) {
    $("#m_repeater_2").repeater({
		initEmpty: !1,
		defaultValues: {
			"text-input": "foo"
		},
		show: function() {
			$(this).slideDown()
		},
		hide: function(e) {
			confirm("Are you sure you want to delete this element?") && $(this).slideUp(e)
		}
	});
	<?php if($QUESTION_ID != 0): ?>
	var el = document.getElementById('options-list');
	var sortable = Sortable.create(el, {
		draggable: ".dragable-item",
		handle: ".item-handle"
	});
	<?php endif; ?>
	
	$(document).on("change", '#Questions_id', function() {
		$.post('/ajax/questions.mgt.php?action=getQuestion', {
			qid: $(this).val()
		}, function(data) {	
			$('#PrefQuestions_mappedMatchField').val(data.MappedField);
			$('#QuestionTypes_id').val(data.QuestionTypes_id);
			$('#options-list').html(data.html);
		}, "json");
	});
});
function submitQform() {
	var aArray = new Array;
	var formData = $('#question-form').serializeArray();
	var index = 0;
	$.post('/ajax/questions.mgt.php?action=submitPrefQuestion', formData, function(data) {
		if(data.newRecord) {
			alert('Question Created...');
			document.location.href='/pmanage/'+data.newQID;
		} else {
			alert('Question Updated...');
			document.location.reload(true);
		}
	}, "json");
}
</script>