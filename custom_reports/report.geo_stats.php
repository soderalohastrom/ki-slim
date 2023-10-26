<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sales.php");
include_once("class.encryption.php");
include_once("class.sessions.php");
include_once("class.dashboard.php");
$DB = new database();
$DB->connect();

$RECORD = new Record($DB);
$SALES = new Sales($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);
$DASHBOARD = new Dashboard($DB, $RECORD, $ENC);

?>
<!-- <script src="//maps.google.com/maps/api/js?key=AIzaSyBE3dc8SCYrTsKHAL2o7HwC9uhjoYIKeKE" type="text/javascript"></script>
<script src="/assets/vendors/custom/gmaps/gmaps.js" type="text/javascript"></script> -->
<style>
.infoTitle {
	font-weight:boldest;
	font-size:1.2em;
}
.info-block {
	width:200px;
}
</style>
<?php // $DASHBOARD->widget_GeoOverview(false); ?>