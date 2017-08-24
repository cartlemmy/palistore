<?php

$this->forceHTTPS();
	
require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/store/class.storeOrder.php");
	
$this->setTitle("Order Status");
$this->setCaching(false);
$this->set("store",1);

$store = new store();

$cfg = store::getConfig();

if (isset($request["rawParams"])) {

	$order = new storeOrder($request["rawParams"]);
	
	print_r($order->getShipmentStatus());
	
	exit();
}
