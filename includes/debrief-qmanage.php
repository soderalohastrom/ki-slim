<?php
$QUESTION_ID = $pageParamaters['params'][0];
$TAB = $pageParamaters['params'][1];

$q_sql = "
SELECT
	*
FROM
	Questions
	INNER JOIN QuestionsCategories ON QuestionsCategories.QuestionsCategories_id=Questions.QuestionsCategories_id
	INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id
WHERE
	1
AND
	Questions.Questions_id='".$QUESTION_ID."'
	
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

$catSql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id = 15 ORDER BY QuestionsCategories_id ASC";
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
                    <small>profile questions</small>
                </h3>
            </div>
        </div>
    </div>
    <!--begin::Form-->
    <form class="m-form" id="question-form" name="qForm" action="javascript:submitQform();">
    	<input type="hidden" name="Questions_id" id="Questions_id" value="<?php echo $QUESTION_ID?>" />
        <div class="m-portlet__body">
            <div class="m-form__section m-form__section--first">
                <div class="form-group m-form__group row">
                    <label class="col-lg-2 col-form-label">
                        Question:
                    </label>
                    <div class="col-lg-10">
                        <input type="text" name="Questions_text" id="Questions_text" class="form-control m-input" value="<?php echo $Q_DATA['Questions_text']?>" required="required">
                        <span class="m-form__help">
                            Please enter the full question text as it will appear in profile and in forms.
                        </span>
                    </div>
                </div>
                <div class="row">
                	<div class="col-lg-6">
                
                        <div class="form-group m-form__group row">
                            <label class="col-lg-4 col-form-label">
                                Category:
                            </label>
                            <div class="col-lg-8">
                                <select name="QuestionsCategories_id" id="QuestionsCategories_id" class="form-control m-input">
                                    <?php echo $catSelect?>
                                </select>
                                <span class="m-form__help">
                                    Region in which quesiton will appear on the profile.
                                </span>
                            </div>
                        </div>                
                        <div class="form-group m-form__group row">
                            <label class="col-lg-4 col-form-label">
                                Type:
                            </label>
                            <div class="col-lg-8">
                                <select name="QuestionTypes_id" id="QuestionTypes_id" class="form-control m-input">
                                    <?php echo $typeSelect?>
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
                                        <input type="radio" name="Questions_active" value="1" <?php echo (($Q_DATA['Questions_active'] == 1)? 'checked':'')?>>
                                        Active
                                        <span></span>
                                    </label>
                                    <label class="m-radio">
                                        <input type="radio" name="Questions_active" value="0" <?php echo (($Q_DATA['Questions_active'] == 0)? 'checked':'')?>>
                                        Inactive
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
					</div>
                    <div class="col-lg-6">
                    	<div id="m_repeater_2">
                            <div class="form-group  m-form__group row">
                                <label  class="col-lg-3 col-form-label">
                                    Options:
                                </label>
                                <?php if($QUESTION_ID != 0): ?>
                                <div data-repeater-list="" class="col-lg-6" id="options-list">
                                    <?php foreach($a_array as $answer):?>
                                    <div data-repeater-item class="m--margin-bottom-10 dragable-item">
                                        <div class="input-group">
                                            <span class="input-group-addon item-handle">
                                                <i class="la la-bars"></i>
                                            </span>
                                            <input type="text" name="answers" value="<?php echo $answer?>" class="form-control form-control-danger form-answer-item">
                                            <span class="input-group-btn" data-repeater-delete="">
                                                <a href="#" class="btn btn-danger m-btn m-btn--icon">
                                                    <i class="la la-close"></i>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="row">
                                <div class="col-lg-3"></div>
                                <?php if($QUESTION_ID == 0): ?>
                                <div class="col">
                                    <div class="alert alert-danger">You must first save this new question before you can add possible answers. Don't worry, you will be brought back here automaticly</div>
                                </div>                                
                                <?php else: ?>                                
                                <div class="col">
                                	<?php if(($Q_DATA['QuestionTypes_name'] == 'SELECT') || ($Q_DATA['QuestionTypes_name'] == 'RADIO') || ($Q_DATA['QuestionTypes_name'] == 'CHECKBOX')): ?>
                                    <div data-repeater-create="" class="btn btn btn-warning m-btn m-btn--icon">
                                        <span>
                                            <i class="la la-plus"></i>
                                            <span>
                                                Add
                                            </span>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
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
                    	<a href="/debrief-manager" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Questions</a>
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
});
function submitQform() {
	var aArray = new Array;
	var formData = $('#question-form').serializeArray();
	var index = 0;
	<?php if($QUESTION_ID != 0): ?>
	$('.form-answer-item').each(function() {
		if($(this).val() != '') {
			//aArray[index] = ;
			//index++;
			formData.push({name: 'answers[]', value: $(this).val() });
		}
	});
	
	<?php endif; ?>
	$.post('/ajax/questions.mgt.php?action=submitQuestion', formData, function(data) {
		if(data.newRecord) {
			alert('Question Created...');
			document.location.href='/debrief-qmanage/'+data.newQID;
		} else {
			alert('Question Updated...');
			document.location.reload(true);
		}
	}, "json");
}
</script>