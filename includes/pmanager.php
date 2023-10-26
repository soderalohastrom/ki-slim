<?php
include_once("class.users.php");
$USER = new Users($DB);

$p_sql = "
SELECT
	*
FROM
	PrefQuestions
	INNER JOIN QuestionTypes ON QuestionTypes.QuestionTypes_id=PrefQuestions.QuestionTypes_id
WHERE
	PrefQuestions_cat='Profile'
";
$p_snd = $DB->get_multi_result($p_sql);
$U_DATA = json_encode($p_snd, JSON_HEX_APOS);


$catSql = "SELECT * FROM QuestionsCategories ORDER BY QuestionsCategories_id ASC";
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
                        preference questions
                    </small>
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">
                <li class="m-portlet__nav-item">
                    <a href="/pmanage/0" class="m-portlet__nav-link btn btn-accent m-btn m-btn--pill">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <div class="m-form__group m-form__group--inline">
                                <div class="m-form__label">
                                    <label class="m-label m-label--single">Portal:</label>
                                </div>
                                <div class="m-form__control">
                                    <select class="form-control m-bootstrap-select m-bootstrap-select--solid" id="m_form_type">
                                        <option value="1" selected="selected">Yes</option>
                                        <option value="0">No</option>
                                        <option value="">All</option> 
                                    </select>
                                </div>
                            </div>
                            <div class="d-md-none m--margin-bottom-10"></div>
                        </div>
                        <div class="col-md-4">
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
$q_sql = "SELECT * FROM PrefQuestions WHERE PrefQuestion_active='1' AND PrefQuestions_cat='Profile' ORDER BY PrefQuestions_order ASC";
$q_found = $DB->get_multi_result($q_sql, true);
if($q_found > 0) {
	$q_snd = $DB->get_multi_result($q_sql);	
	?><ul class="list-group" id="SortQuestions_profile"><?php
	if(!isset($q_snd['empty_result'])):
		foreach($q_snd as $q_dta):
		?>
		<li class="list-group-item dragable-item">
			<input type="hidden" name="QuestionOrder[]" value="<?php echo $q_dta['PrefQuestion_id']?>" />
			<?php echo $q_dta['PrefQuestion_text']?>
		</li>
		<?php
		endforeach;
	endif;
	?></ul><?php
}
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
                    field: "PrefQuestion_id",
                    title: "#",
                    width: 35,
                    sortable: !1,
                    selector: !1,
                    textAlign: "center"
                }, {
                    field: "PrefQuestion_text",
                    title: "Question"
                }, {
					field: "PrefQuestion_active",
					title: "Active",
					width: 75,
					template: function(e) {
						var b = {
							1: {
								title: "Active",
								class: "m-badge--danger"
							},
							0: {
								title: "Inactive",
								class: "m-badge--info"
							}
						};
					return '<span class="m-badge ' + b[e.PrefQuestion_active].class + ' m-badge--wide">' + b[e.PrefQuestion_active].title + '</span>'
					}
				}, {
					field: "PrefQuestions_inPortal",
					title: "Portal",
					width: 50,
					template: function(e) {
						var d = {
							1: {
								title: "Yes",
								class: "m-badge--primary"
							},
							0: {
								title: "No",
								class: "m-badge--metal"
							}
						};
					return '<span class="m-badge ' + d[e.PrefQuestions_inPortal].class + ' m-badge--wide">' + d[e.PrefQuestions_inPortal].title + '</span>'
					}
				}, {                    
                    field: "Actions",
                    width: 75,
                    title: "Actions",
                    sortable: !1,
                    overflow: "visible",
                    template: function(e) {
                        return '<a href="/pmanage/'+e.PrefQuestion_id+'" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="View "><i class="la la-edit"></i></a>\t\t\t\t\t'
                    }
                }],				
            }),
            i = a.getDataSourceQuery();
			$('#m_form_stat').on("change", function() {
				a.search($(this).val(), "PrefQuestion_active")
			}).val(void 0 !== i.PrefQuestion_active ? i.PrefQuestion_active : "1"),
			
			$("#m_form_type").on("change", function() {
            	a.search($(this).val(), "PrefQuestions_inPortal")
        	}).val(void 0 !== i.PrefQuestions_inPortal ? i.PrefQuestions_inPortal : ""), 
			
			$("#m_form_stat, #m_form_cat, #m_form_type").selectpicker()
			a.search(1, "PrefQuestion_active")
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
    DatatableDataLocalDemo.init();
	var sortable = Sortable.create(document.getElementById('SortQuestions_profile'), {
		draggable: ".dragable-item"
	});
});
function saveOrder() {
	var formData = $('#q-sort-form').serializeArray();
	console.log(formData);
	$.post('/ajax/questions.mgt.php?action=savePrefOrder', formData, function(data) {
		alert('Pref Question Order Saved');
		$('#qOrderModal').modal('hide');		
	});
	
}
</script>