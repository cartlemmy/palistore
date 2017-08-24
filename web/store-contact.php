<?php

$this->forceHTTPS();

require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/store/class.storeOrder.php");

$this->setTitle("Contact");
$this->setCaching(false);
$this->set("store",1);

$cfg = store::getConfig();

if (isset($request["rawParams"])) {
	$order = new storeOrder($request["rawParams"]);
	if ($order && $order->isValid() && $order->isOrdered()) {

		require(SL_WEB_PATH."/inc/store-contact.php");
	}
}

