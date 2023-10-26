<?php
include_once("class.users.php");
$USER = new Users($DB);

$u_sql = "
SELECT
	Questions.Questions_id,
	Questions.Questions_text,
	Questions.Questions_order,
	Questions.QuestionTypes_id,
	Questions.QuestionsCategories_id,
	Questions.Questions_active,
	Questions.Questions_order,
	Questions.Questions_inPortal,
	QuestionsCategories.QuestionsCategories_name,
	QuestionTypes.QuestionTypes_name
FROM
	Questions
	INNER JOIN QuestionsCategories ON QuestionsCategories.QuestionsCategories_id=Questions.QuestionsCategories_id
	INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=Questions.QuestionTypes_id
WHERE
	1
AND
	QuestionsCategories.QuestionsCategories_id != 15
ORDER BY
	Questions.Questions_order ASC
";
//echo $u_sql;
$u_snd = $DB->get_multi_result($u_sql);
$U_DATA = json_encode($u_snd, JSON_HEX_APOS);


$catSql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id != 15 ORDER BY QuestionsCategories_id ASC";
$catSnd = $DB->get_multi_result($catSql);
ob_start();
foreach($catSnd as $catDta):
	?><option value="<?php echo $catDta['QuestionsCategories_id']?>"><?php echo $catDta['QuestionsCategories_name']?></option><?php
endforeach;
$catSelect = ob_get_clean();

$typeSQL = "SELECT * FROM QuestionTypes ORDER BY QuestionTypes_id ASC";
$typeSnd = $DB->get_multi_result($typeSQL);
ob_start();
foreach($typeSnd as $typeDta):
	?><option value="<?php echo $typeDta['QuestionTypes_id']?>"><?php echo $typeDta['QuestionTypes_name']?></option><?php
endforeach;
$typeSelect = ob_get_clean();
?>
<script src="/assets/vendors/custom/sortable-master/Sortable.min.js" type="text/javascript"></script>
<div class="m-content">
<div class="m-portlet m-portlet--mobile">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
                    Question Manager
                    <small>
                        profile questions
                    </small>
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">
                <li class="m-portlet__nav-item">
                    <a href="/qmanage/0" class="m-portlet__nav-link btn btn-accent m-btn m-btn--pill">
						<i class="la la-user-plus"></i>
						<span>
							New Question
						</span>
					</a>
                    &nbsp;
                    <a href="#" class="m-portlet__nav-link btn btn-metal m-btn m-btn--pill" data-toggle="modal" data-target="#qOrderModal">
						<i class="la la-sort-amount-desc"></i>
						<span>
							Sort &amp; Organize
						</span>
					</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="m-portlet__body">
        <!--begin: Search Form -->
        <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
            <div class="row align-items-center">
                <div class="col-xl-12 order-2 order-xl-1">
                    <div class="form-group m-form__group row align-items-center">
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label>
                                        Status:
                                    </label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_stat">
                                        <option value="1" selected="selected">Active</option>
                                        <option value="0">Inactive</option>
                                        <option value="">All</option>                                        
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label>
                                        Category:
                                    </label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_cat">
                                        <option value="">All</option>
                                        <?php echo $catSelect?>                                        
                                    </select>
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
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_type">
                                        <option value="">All</option>
										<?php echo $typeSelect?>
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-input-icon m-input-icon--left">
                                <input type="text" class="form-control m-input m-input--solid" placeholder="Search..." id="generalSearch">
                                <span class="m-input-icon__icon m-input-icon__icon--left">
                                    <span>
                                        <i class="la la-search"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
				<!--
                <div class="col-xl-4 order-1 order-xl-2 m--align-right">
                    <a href="/users/0" class="btn btn-accent m-btn m-btn--custom m-btn--icon m-btn--pill">
                        <span>
                            <i class="la la-user-plus"></i>
                            <span>
                                New User
                            </span>
                        </span>
                    </a>
                    <div class="m-separator m-separator--dashed d-xl-none"></div>
                </div>
				-->
            </div>
        </div>
        <!--end: Search Form -->
<!--begin: Datatable -->
        <div class="m_datatable" id="local_data"></div>
        <!--end: Datatable -->
    </div>
</div>
</div>

<div class="modal fade" id="qOrderModal" tabindex="-1" data-backdrop="static" role="dialog" aria-labelledby="qOrderModalLabel" aria-hidden="true">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="qOrderModalLabel">Questions Order &amp; Sorting</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
            <form id="q-sort-form">
                      
<?php
$cat_sql = "SELECT * FROM QuestionsCategories WHERE QuestionsCategories_id != 15 ORDER BY QuestionsCategories_id ASC";
$cat_snd = $DB->get_multi_result($cat_sql);
foreach($cat_snd as $cat_dta):
	$q_sql = "SELECT * FROM Questions WHERE QuestionsCategories_id='".$cat_dta['QuestionsCategories_id']."' AND Questions_text != '' AND Questions_active='1' ORDER BY Questions_order ASC";
	$q_found = $DB->get_multi_result($q_sql, true);
	if($q_found > 0) {
		$q_snd = $DB->get_multi_result($q_sql);	
		?><ul class="list-group" id="SortQuestions_<?php echo $cat_dta['QuestionsCategories_id']?>"><?php
    	?><li class="list-group-item list-group-item-primary"><?php echo $cat_dta['QuestionsCategories_name']?></li><?php
		if(!isset($q_snd['empty_result'])):
			foreach($q_snd as $q_dta):
			?>
            <li class="list-group-item dragable-item">
				<input type="hidden" name="QuestionOrder[]" value="<?php echo $q_dta['Questions_id']?>" />
				<?php echo $q_dta['Questions_text']?>
            </li>
			<?php
			endforeach;
		endif;
    	?></ul><?php
	}
endforeach;
?>
  
			</form>
            </div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="saveOrder()">Save</button>
			</div>
		</div>
	</div>
</div>

<script>
var a, e;
var DatatableDataLocalDemo = function() {
    var e = function() {
        var e = JSON.parse('<?php echo $U_DATA?>'),
            a = $(".m_datatable").mDatatable({
                data: {
                    type: "local",
                    source: e,
                    pageSize: 10
                },
                layout: {
                    theme: "default",
                    class: "",
                    scroll: !1,
                    footer: !1
                },
                sortable: !0,
                pagination: !0,
                search: {
                    input: $("#generalSearch")
                },
                columns: [{
                    field: "Questions_id",
                    title: "#",
                    width: 35,
                    sortable: !1,
                    selector: !1,
                    textAlign: "center"
                }, {
					field: "QuestionsCategories_name",
                    title: "Calegory",
					width: 145
                }, {
                    field: "Questions_text",
                    title: "Question"
                }, {
                    field: "QuestionTypes_name",
                    title: "Type",
					width: 75
				}, {
					field: "Questions_inPortal",
					title: "Portal",
					width: 75,
					template: function(e) {
						var b = {
							1: {
								title: "Yes",
								class: "m-badge--danger"
							},
							0: {
								title: "No",
								class: "m-badge--info"
							}
						};
					return '<span class="m-badge ' + b[e.Questions_inPortal].class + ' m-badge--wide">' + b[e.Questions_inPortal].title + '</span>'
					}
				}, {                    
                    field: "Actions",
                    width: 75,
                    title: "Actions",
                    sortable: !1,
                    overflow: "visible",
                    template: function(e) {
                        return '<a href="/qmanage/'+e.Questions_id+'" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
                    }
                }],				
            }),
            i = a.getDataSourceQuery();
			$('#m_form_stat').on("change", function() {
				a.search($(this).val(), "Questions_active")
			}).val(void 0 !== i.Questions_active ? i.Questions_active : "1"),
        	$("#m_form_cat").on("change", function() {
            	a.search($(this).val(), "QuestionsCategories_id")
        	}).val(void 0 !== i.QuestionsCategories_id ? i.QuestionsCategories_id : ""), 
			
			$("#m_form_type").on("change", function() {
            	a.search($(this).val(), "QuestionTypes_id")
        	}).val(void 0 !== i.QuestionTypes_id ? i.QuestionTypes_id : ""), 
			
			$("#m_form_stat, #m_form_cat, #m_form_type").selectpicker()
			a.search(1, "Questions_active")
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
    DatatableDataLocalDemo.init();
	if( $('#SortQuestions_1').length ) {
		var sortable = Sortable.create(document.getElementById('SortQuestions_1'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_2').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_2'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_3').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_3'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_4').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_4'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_5').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_5'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_6').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_6'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_7').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_7'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_8').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_8'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_9').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_9'), {
			draggable: ".dragable-item"
		});
	}
	
	if( $('#SortQuestions_10').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_10'), {
			draggable: ".dragable-item"
		});
	}
	if( $('#SortQuestions_11').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_11'), {
			draggable: ".dragable-item"
		});
	}
	if( $('#SortQuestions_12').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_12'), {
			draggable: ".dragable-item"
		});
	}
	if( $('#SortQuestions_13').length ) {
		var sortable4 = Sortable.create(document.getElementById('SortQuestions_13'), {
			draggable: ".dragable-item"
		});
	}
});
function saveOrder() {
	var formData = $('#q-sort-form').serializeArray();
	console.log(formData);
	$.post('/ajax/questions.mgt.php?action=saveOrder', formData, function(data) {
		alert('Profile Question Order Saved');
		$('#qOrderModal').modal('hide');		
	});
	
}
</script>