<?php
session_start();
include_once("class.db.php");
include_once("class.record.php");
include_once("class.sessions.php");
include_once("class.encryption.php");

$DB = new database();
$DB->connect();
$DB->setTimeZone();
$RECORD = new Record($DB);
$ENC = new encryption();
$SESSION = new Session($DB, $ENC);

$SESSION->pushSessionExpire();
$json['success'] = true;
echo json_encode($json);
?>