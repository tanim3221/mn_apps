<?php
error_reporting(1);

require_once __DIR__.'/../../sdk/bdapps_cass_sdk.php';

$appId = "APP_084629";
$password = "aa80752ee1226b2fed96d1f1614bebb3";
$server = "";

$subscriber = new Subscription($server,$password,$appId);

$getNumber = $_GET['address'];

$getStatus = $subscriber->getStatus($getNumber);

print_r($getStatus);