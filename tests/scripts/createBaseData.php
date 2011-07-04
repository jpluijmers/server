<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

$serviceUrl = 'http://localhost/';  //Default url is local host if no prameter is given
if(isset($argv[1]))
	$serviceUrl = $argv[1];
else
	print("Service url wasn't inserted using default: http://localhost/");

$config = new KalturaConfiguration();
$config->serviceUrl = $serviceUrl;
//$config->serviceUrl = 'http://hudsontest2.kaltura.dev/';
//$config->serviceUrl = 'http://devtests.kaltura.dev/';
$client = new KalturaClient($config);
$cmsPassword = 'Roni123!';
$partner = new KalturaPartner();
$partner->name = 'Test Partner';
$partner->adminName = 'Test admin name'; 
$partner->adminEmail = "test@mailinator.com";
$partner->description = "partner for tests";

$newPartner = $client->partner->register($partner, $cmsPassword); //create the new test partner

print("New test partner is: " . print_r($newPartner, true));

//Save the partner id into the global data file
KalturaGlobalData::setData("@SERVICE_URL@", $config->serviceUrl);
KalturaGlobalData::setData("@TEST_PARTNER_ID@", $newPartner->id);
KalturaGlobalData::setData("@TEST_PARTNER_ADMIN_SECRET@", $newPartner->adminSecret);
KalturaGlobalData::setData("@TEST_PARTNER_SECRET@", $newPartner->secret);

$config->partnerId = $newPartner->id; //Set the new test partner id
$client = new KalturaClient($config);

$ks = $client->session->start($newPartner->adminSecret, null, KalturaSessionType::ADMIN, $newPartner->id, null, null);
$client->setKs($ks);

$uiConfs = $client->uiConf->listAction();
KalturaGlobalData::setData("@UI_CONF_ID@", $uiConfs->objects[0]->id);

$accessControls = $client->accessControl->listAction();
KalturaGlobalData::setData("@DEFAULT_ACCESS_CONTROL@", $accessControls->objects[0]->id);
