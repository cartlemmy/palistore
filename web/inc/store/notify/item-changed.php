<?php

$order = new storeOrder($ob["item"]->getOI("orderId"),true);

$sess = $ob["item"]->getOI("sess");
//store::adminNotification("item-changed","Item Updated, ".$sess["name"].", Order #".$order->getOrderNumber(),$cfg["fromEmail"]["name"]." <".$cfg["fromEmail"]["email"].">,Josh Merritt <itsupport@palimountain.com>",$ob,$order);
