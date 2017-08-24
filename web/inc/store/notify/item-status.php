<?php

$order = new storeOrder($ob->getOI("orderId"),true);

if (preg_match("/item-status\..*/",$hook)) {
	$sess = $ob->getOI("sess");
	//store::adminNotification("item-status-update","Item ".valueToString($ob->getOI("status"),"db/storeOrderItems/status").", ".$sess["name"].", Order #".$order->getOrderNumber(),$cfg["fromEmail"]["name"]." <".$cfg["fromEmail"]["email"].">,Josh Merritt <itsupport@palimountain.com>",$ob,$order);
}
