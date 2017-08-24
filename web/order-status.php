<?php

$this->forceHTTPS();
	
require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/store/class.storeOrder.php");

require_once(SL_WEB_PATH.'/inc/store/anet_php_sdk/AuthorizeNet.php');

$this->setTitle("Order Status");
$this->setCaching(false);
$this->set("store",1);

$store = new store();

$cfg = store::getConfig();

if (isset($request["rawParams"]) || isset($_GET["id"])) {

	if (isset($_GET["id"])) {
		if ($GLOBALS["slSession"]->user->hasPermission("super OR admin")) {
			$order = new storeOrder((int)$_GET["id"], true);
		} else {
			print_r( $GLOBALS["slSession"]->user );
			return;
		}
	} else {
		$order = new storeOrder($request["rawParams"]);
	}
	$order->store = $store;
	
	if (isset($_POST["send-message"]) && trim($_POST["message"])) {
	ob_start();
	?><table><tbody>
		<tr>
			<td>Order #</td>
			<td><?=$order->getOrderNumber();?></td>
		</tr>
		<tr>
			<td>Name</td>
			<td><?=htmlspecialchars($_POST["name"]);?></td>
		</tr>
		<tr>
			<td>E-mail</td>
			<td><?=htmlspecialchars($order->get("email"));?></td>
		</tr>
		<tr>
			<td>Message</td>
			<td><?=htmlspecialchars($_POST["message"]);?></td>
		</tr>
	</tbody></table><?php
		store::sendEmail($cfg["fromEmail"]["email"],$cfg["fromEmail"]["name"],"Order Contact - #".$order->getOrderNumber(),ob_get_clean(),$order->get("email"),$_POST["name"],true);
		?><div class="success">en-us|Your message has been sent, we will respond as soon as possible.</div><?php
	}
	
	if ($order->isValid() && $order->isOrdered()) {

		$cart = new storeCart($order->id,true);
		$order->updateAddresses();
		$cart->updateOrder($order);		

		require(SL_WEB_PATH."/inc/store/template/receipt.php");
	} else {
		?><div class="error">There was an issue retrieving your order<br /><strong>(<?=valueToString($order->get("status"),"db/storeOrders/status");?>)</strong></div><?php
	}
}
