<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");

$DB = new database();
$DB->connect();
$RECORD = new Record($DB);


            ?>
<link href="/custom_reports/styles/kendo.common.min.css" rel="stylesheet">
<link href="/custom_reports/styles/kendo.rtl.min.css" rel="stylesheet">
<link href="/custom_reports/styles/kendo.default.min.css" rel="stylesheet">
<link href="/custom_reports/styles/kendo.default.mobile.min.css" rel="stylesheet">
<script src="/custom_reports/js/jszip.min.js"></script>
<script src="/custom_reports/js/kendo.all.min.js"></script>
<script src="/assets/vendors/custom/tablesorter/dist/js/jquery.tablesorter.min.js" type="text/javascript"></script>
<link href="/assets/vendors/custom/tablesorter/dist/css/theme.bootstrap_4.min.css" rel="stylesheet" type="text/css" />

<div class="k-toolbar">
    <div>
    <label for="reportDateRange" class="k-checkbox-label">Next Action On</label>
         <input type="text" name="reportDateRange" id="reportDateRange" class="k-daterangepicker-wrap" value="" placeholder="" autocomplete="off">
    </div>
    <div>
    <label for="datestatuses" class="k-checkbox-label">Date Status</label>
         <input id="datestatuses" />
    </div>
    <div>
        <input type="checkbox" id="hideincompleted" />
    </div>
</div>

<div id="report">
    <div id="grid2"></div>
</div>

<script>
    $(document).ready(function() {
        var dateStatuses = [
            { text: "All", value: "All" },
            { text: "Match to Explore", value: "Match to Explore" },
            { text: "Match OK'd", value: "Match OK'd"  },
            { text: "Match on Hold", value: "Match on Hold" },
            { text: "Match Completed", value: "Match Completed" },
            { text: "Match Declined", value: "Match Declined" },
            { text: "Match Connected", value: "Match Connected" },
            { text: "Met/Need Feedback", value: "Met/Need Feedback"},
            { text: "Match Idea", value: "Match Idea"},
            { text: "Declined by Matchmaker", value:  "Declined by Matchmaker" },
        ];

        // create DropDownList from input HTML element
        $("#datestatuses").kendoDropDownList({
            lebel: "Date Status",
            dataTextField: "text",
            dataValueField: "value",
            dataSource: dateStatuses,
            index: 0,
            change: onChange
        });
                
        $('#hideincompleted').kendoCheckBox({
            checked: false,
            label: "Show Completed Only",
            enabled: true,
            change: function (e) {
             
            var grid = $("#grid2").data("kendoGrid");
            // Change the name of the first dataItem.
            grid.dataSource.options.transport.read.data.hideincomplete = e.checked;
            console.log(grid);
            // Call refresh in order to see the change.
            grid.dataSource.read(); 
            grid.refresh();
            }
        });

        var start = moment().subtract(30, 'days');
        var end = moment();
        $('#reportDateRange').daterangepicker({
            buttonClasses: 'm-btn btn',
            applyClass: 'btn-primary',
            cancelClass: 'btn-secondary',
            label: 'Next Action On',
            startDate: start,
            endDate: end,
            ranges: {
                'Today'			: [moment().subtract(1, 'days'), moment()],
                'This Week'		: [moment().startOf('week'), moment().endOf('week')],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last 7 Days': [moment().subtract(7, 'days'), moment()],
                'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                'Last 60 Days': [moment().subtract(60, 'days'), moment()],
                'Last 90 Days': [moment().subtract(90, 'days'), moment()],
                'Last 6 Months': [moment().subtract(6, 'months'), moment()],
                'Last 12 Months': [moment().subtract(12, 'months'), moment()],
                'Year to Date': [moment('2023-01-01T00:00:00-08:00'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last Year to Date': [moment('2022-01-01T00:00:00-08:00'), moment('2022-12-31T00:00:00-08:00')],
            }
        });
        $('#reportDateRange').on('apply.daterangepicker', function(ev, picker) {
            var grid = $("#grid2").data("kendoGrid");
            // Change the name of the first dataItem.
            grid.dataSource.options.transport.read.data.start = picker.startDate.format("YYYY-MM-DD");
            grid.dataSource.options.transport.read.data.end = picker.endDate.format("YYYY-MM-DD");
             // Call refresh in order to see the change.
            grid.dataSource.read(); 
            grid.refresh();
        });

        function returnFalse() {
            return false;
        }
        $("#grid2").kendoGrid({
            dataSource: {
                transport: {
                    read: {
                        url: "/custom_reports/report.mm_dates_data.php?",
                        data: {
                            format: "json"
                        },
                        dataType: "json", // "jsonp" is required for cross-domain requests; use "json" for same-domain requests

                    },
                },
                batch: true,
                pageSize: 1000,
                autoSync: true,
                schema: {
                    model: {
                        id: "PersonsDates_id",
                        fields: {
                            PersonsDates_id: {
                                editable: false,
                                nullable: true
                            },
                            Participant1: {
                                editable: false,
                                nullable: true
                            },
                            Participant2: {
                                editable: false,
                                nullable: true
                            },
                            Participant2_Type: {
                                editable: false,
                                nullable: true
                            },
                            Date_Location: {
                                editable: false,
                                nullable: true
                            },
                            Date_Status: {
                                editable: false,
                                nullable: true
                            },
                            MatchMaker: {
                                editable: false,
                                nullable: true
                            },
                            Participant1_Disposition: {
                                editable: false,
                                nullable: true
                            },
                            Participant2_Disposition: {
                                editable: false,
                                nullable: true
                            },
                            Date_Created: {
                                editable: false,
                                nullable: true,
                                type: "date"
                            },
                            Date_Completed: {
                                editable: false,
                                nullable: true,
                                type: "date"
                            },
                            PersonsDates_isComplete: {
                                editable: false,
                                nullable: true
                            },
                            Next_Action_On: {
                                editable: false,
                                nullable: true,
                                type: "date"
                            },
                        }
                    }
                }
            },
            height: 800,
            sortable: true,
            navigatable: true,
            resizable: true,
            reorderable: true,
            groupable: true,
            filterable: true,
            toolbar: ["excel", "pdf", "search"],
            pageable: {
                refresh: true,
                pageSizes: [100, 1000, 10000, "all"],
                buttonCount: 5
            },
            dataBound: function() {
                for (var i = 0; i < this.columns.length; i++) {
                    this.autoFitColumn(i);
                }
            },
            columns: [{
                    field: "PersonsDates_id",
                    title: "View",

                    filterable: false,
                    template: " 	<a href='/intro/#:data.PersonsDates_id#' class='btn btn-sm btn-secondary'><i class='fa fa-heart'></i></a>",
                }, {
                    field: "Participant1",
                    title: "Participant1",
                },

                {
                    field: "Participant2",
                    title: "Participant2"
                },
                {
                    field: "Participant2_Type",
                    title: "Participant2_Type"
                },
                {
                    field: "Date_Location",
                    title: "Date_Location"
                },
                {
                    field: "Date_Status",
                    title: "Date_Status"
                },
                {
                    field: "RelationshipManager",
                    title: "Relationship Manager"
                },
                {
                    field: "NetworkDeveloper",
                    title: "Network Developer"
                },
                {
                    field: "Participant1_Disposition",
                    title: "Participant1_Disposition"
                },
                {
                    field: "Participant2_Disposition",
                    title: "Participant2_Disposition"
                },
                {
                    field: "Date_Created",
                    title: "Date_Created",
                    format: "{0:MM/dd/yyyy}"
                },
                {
                    field: "Date_Completed",
                    title: "Date_Completed",
                    format: "{0:MM/dd/yyyy}"
                },
                {
                    field: "PersonsDates_isComplete",
                    title: "PersonsDates_isComplete"
                },
                {
                    field: "Next_Action_On",
                    title: "Next_Action_On",
                    format: "{0:MM/dd/yyyy}",
                },
            ]
        });

    });

    function Refresh() {
        $("#grid2").data("kendoGrid").dataSource.read();
        $("#grid2").data("kendoGrid").refresh();
    }

    function onChange() {
        var value = $("#datestatuses").val();
        //console.log(ev);
        var grid = $("#grid2").data("kendoGrid");
        // Change the name of the first dataItem.
        grid.dataSource.options.transport.read.data.statuses = value;
        // Call refresh in order to see the change.
        grid.dataSource.read(); 
        grid.refresh();
    };
</script>

    <style type="text/css">
        .customer-photo {
            display: inline-block;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-size: 32px 35px;
            background-position: center center;
            vertical-align: middle;
            line-height: 32px;
            box-shadow: inset 0 0 1px #999, inset 0 0 10px rgba(0, 0, 0, .2);
            margin-left: 5px;
        }

        .customer-name {
            display: inline-block;
            vertical-align: middle;
            line-height: 32px;
            padding-left: 3px;
        }

        .k-grid tr .checkbox-align {
            text-align: center;
            vertical-align: middle;
        }

        .product-photo {
            display: inline-block;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-size: 32px 35px;
            background-position: center center;
            vertical-align: middle;
            line-height: 32px;
            box-shadow: inset 0 0 1px #999, inset 0 0 10px rgba(0, 0, 0, .2);
            margin-right: 5px;
        }

        .product-name {
            display: inline-block;
            vertical-align: middle;
            line-height: 32px;
            padding-left: 3px;
        }

        .k-rating-container .k-rating-item {
            padding: 4px 0;
        }

        .k-rating-container .k-rating-item .k-icon {
            font-size: 16px;
        }

        .dropdown-country-wrap {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            white-space: nowrap;
        }

        .dropdown-country-wrap img {
            margin-right: 10px;
        }

        #grid .k-grid-edit-row>td>.k-rating {
            margin-left: 0;
            width: 100%;
        }
    </style>
