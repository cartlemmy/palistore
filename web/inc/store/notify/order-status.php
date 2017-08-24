<?php
require_once(SL_WEB_PATH."/inc/store/class.storeCart.php");

$cart = new storeCart($ob->get("id"));
$cart->updateOrder($ob);

if (preg_match("/order-status\..*/",$hook)) {
	store::adminNotification("order-status-update","Order ".valueToString($ob->get("status"),"db/storeOrderItems/status").", Order #".$ob->getOrderNumber(),$cfg["fromEmail"]["name"]." <".$cfg["fromEmail"]["email"].">",$ob,$cart);
}
