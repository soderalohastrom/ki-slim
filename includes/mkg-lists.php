<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("class.marketing.php");
$marketing = new Marketing();
$ml_sql = "SELECT 
			MarketingLists_id, 
			MarketingLists_name, 
			ifNull(MarketingListCategories_name, 'Uncategorized') AS  MarketingListCategories_name,
			MarketingLists_dateCreated,
			MarketingLists_active 
		FROM MarketingLists 
		LEFT JOIN MarketingListCategories ON MarketingLists_category = MarketingListCategories_id
		ORDER BY MarketingLists_name";
$ml_snd = $DB->get_multi_result($ml_sql);
if(!array_key_exists('error', $ml_snd) && !array_key_exists('empty_result', $ml_snd)) {
	$ml_data = array();
	foreach($ml_snd as $ml_row) {
		$new_row = array();
		foreach($ml_row as $row_col=>$row_val) {
			$new_row[$row_col] = $row_val; //str_replace("'", "\'", $row_val);
			if($row_col == 'MarketingLists_dateCreated') {
				$new_row['MarketingLists_dateFormatted'] = date('n/d/Y', $row_val);
			}
		}
		$ml_data[] = $new_row;
	}
	$ml_data_json = json_encode($ml_data);
} else {
	$ml_data_json = false;
}
?>
<script src="/assets/app/js/marketing.js" type="text/javascript"></script>
<!--
<div class="m-subheader">
<div class="btn-group pull-right">
	<button type="button" class="btn btn-accent" onclick="javascript:document.location='/mkg-list/0'"><i class="flaticon-add" aria-hidden="true"></i> Create Marketing List</button>
</div>
<h3>Marketing Lists</h3>
</div>
-->
<div class="m-content">
<div class="m-portlet m-portlet--mobile">	
	<div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
					<i class="flaticon-list-3"></i>
                    Marketing Lists
                </h3>
            </div>
        </div>
        <div class="m-portlet__head-tools">
            <ul class="m-portlet__nav">
                <li class="m-portlet__nav-item">
                    <a href="/mkg-list/0" class="m-portlet__nav-link btn btn-accent m-btn m-btn--pill">
						<i class="flaticon-add"></i>
						<span>
							Create Marketing List
						</span>
					</a>
                </li>
            </ul>
        </div>
    </div>
	<div class="m-portlet__body">
	<?php if($ml_data_json !== false) { ?>
			<!--begin: Search Form -->
			<div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
				<!--<div class="row align-items-center">
					<div class="col-xl-8 order-2 order-xl-1">-->
						<div class="form-group m-form__group row align-items-center">
							<div class="col-md-4">
								<div class="m-input-icon m-input-icon--left">
									<input type="text" class="form-control m-input" placeholder="Search..." id="m_form_listname">
									<span class="m-input-icon__icon m-input-icon__icon--left">
										<span>
											<i class="la la-search"></i>
										</span>
									</span>
								</div>
								<span class="m-form__help">
									search results for text
								</span>
							</div>
							<div class="col-md-4">
								<div class="m-form__control">
									<select class="form-control m-bootstrap-select" id="m_form_status">
										<option value="">
											All
										</option>
										<option value="1">
											Active
										</option>
										<option value="0">
											Inactive
										</option>
									</select>
								</div>
								<span class="m-form__help">
									filter on status
								</span>
						</div>
					</div>
			</div>
			<!--end: Search Form -->
<!--begin: Datatable -->
			<div class="m_datatable" id="local_data"></div>
			<!--end: Datatable -->
		<?php } else { ?>
			<div class="alert alert-info">No marketing lists found.</div>
		<?php } ?>
		</div>
	</div>
</div>
<script>
var DatatableMkgLists = function() {
    var e = function() {
       	var e = JSON.parse('<?php echo str_replace("'", "\'", $ml_data_json); ?>'),
		a = $(".m_datatable").mDatatable({
                data: {
                    type: "local",
                    source: e,
                    pageSize: 10,
					saveState: {
						cookie: false,
						webstorage: false
					},
                },
                layout: {
                    theme: "default",
                    class: "",
                    scroll: !1,
                    height: 450,
                    footer: !0
                },
                sortable: !0,
                pagination: !0,
                search: {
                    input: $("#m_form_listname")
                },
                columns: [{
                    field: "MarketingLists_id",
                    title: "List ID",
					width: 70
                }, {
                    field: "MarketingLists_name",
                    title: "List Name"
				}, {
                    field: "MarketingListCategories_name",
                    title: "Category"
                }, {
                    field: "MarketingLists_dateFormatted",
                    title: "Date Created",
					type: "date",
                    format: "MM/DD/YYYY"
				 }, {
                    field: "MarketingLists_active",
                    title: "Status",
					template: function(e) {
                        var a = {
                            0: {
                                title: "Inactive",
                                class: "m-badge--metal"
                            },
                            1: {
                                title: "Active",
                                class: " m-badge--primary"
                            }
                        };
                        return '<span class="m-badge ' + a[e.MarketingLists_active].class + ' m-badge--wide">' + a[e.MarketingLists_active].title + "</span>"
                    }
				}, {
                    field: "Actions",
                    title: "Actions",
					width: 70,
					sortable: !1,
					template: function(e) {
                        return '<a href="/mkg-list/' + e.MarketingLists_id + '" class="m-portlet__nav-link btn m-btn m-btn--hover-accent m-btn--icon m-btn--icon-only m-btn--pill" title="Edit/View"><i class="la la-edit"></i></a>&nbsp;<a href="javascript:void(delete_list(' + e.MarketingLists_id + '))" class="m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete"><i class="la la-trash"></i></a>'
                    }
                }]
            }),
            i = a.getDataSourceQuery();
        $("#m_form_status").on("change", function() {
            a.search($(this).val(), "MarketingLists_active")
        }),
		/* THIS DOES NOT WORK!!!!!! $("#m_form_listname").on("keyup", function() {
            a.search($(this).val(), "MarketingLists_name")
        }),*/
		$("#m_form_status").selectpicker()
    };
    return {
        init: function() {
            e()
        }
    }
}();
jQuery(document).ready(function() {
    DatatableMkgLists.init()
});
</script>