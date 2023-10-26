<div class="m-content">
    <div class="row">
        <div class="col-lg-12">
        <!--begin:: Portlet/My Calendar-->
		<div class="m-portlet" id="m_portlet">
			<div class="m-portlet__head">
				<div class="m-portlet__head-caption">
					<div class="m-portlet__head-title">
						<span class="m-portlet__head-icon">
							<i class="flaticon-time-3"></i>
						</span>
						<h3 class="m-portlet__head-text">
							My Calendar
						</h3>
					</div>
				</div>
			</div>
			<div class="m-portlet__body">
				<div id="m_calendar"></div>
			</div>
		</div>
		<!--end:: Portlet/My Calendar-->
		<script>
		var MyCalendar = {
			init: function() {
				var e = moment().startOf("day"),
					t = e.format("YYYY-MM"),
					i = e.clone().subtract(1, "day").format("YYYY-MM-DD"),
					n = e.format("YYYY-MM-DD"),
					r = e.clone().add(1, "day").format("YYYY-MM-DD");
				$("#m_calendar").fullCalendar({
					header: {
						left: "prev,next today",
						center: "title",
						right: "month,agendaWeek,agendaDay,listWeek"
					},
					editable: !0,
					eventLimit: !0,
					navLinks: !0,
				    eventSources: [{
						url:'/ajax/cal_feed.php'
					},{
						url:'/ajax/cal_matchmakers.php'													
					}],
					eventRender: function(e, t) {
						t.hasClass("fc-day-grid-event") ? (t.data("content", e.description), t.data("placement", "top"), mApp.initPopover(t)) : t.hasClass("fc-time-grid-event") ? t.find(".fc-title").append('<div class="fc-description">' + e.description + "</div>") : 0 !== t.find(".fc-list-item-title").lenght && t.find(".fc-list-item-title").append('<div class="fc-description">' + e.description + "</div>")
					}
				})
			}
		};
		$(document).ready(function() {
			MyCalendar.init();
		});
		</script>
        </div>
    </div>
</div>
<script>
var CalendarGoogle = {
    init: function() {
		$("#m_calendar").fullCalendar({
            header: {
                left: "prev,next today",
                center: "title",
                right: "month,listYear"
            },
            displayEventTime: !1,
            googleCalendarApiKey: "",
            events: "rich@kelleher-international.com",
            eventClick: function(e) {
                return window.open(e.url, "gcalevent", "width=800,height=600"), !1
            },
            loading: function(e) {},
            eventRender: function(e, n) {
                e.description && (n.hasClass("fc-day-grid-event") ? (n.data("content", e.description), n.data("placement", "top"), mApp.initPopover(n)) : n.hasClass("fc-time-grid-event") ? n.find(".fc-title").append('<div class="fc-description">' + e.description + "</div>") : 0 !== n.find(".fc-list-item-title").lenght && n.find(".fc-list-item-title").append('<div class="fc-description">' + e.description + "</div>"))
            }
        })
    }
};
jQuery(document).ready(function() {
    CalendarGoogle.init()
});
</script>