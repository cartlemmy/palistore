<?php

$this->forceHTTPS();

require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/store/class.storeOrder.php");

require_once(SL_WEB_PATH.'/inc/store/anet_php_sdk/AuthorizeNet.php');

$this->setTitle("Payment Response");
$this->setCaching(false);

$store = new store();
$order = new storeOrder($store);

$cfg = store::getConfig();

$dataFile = SL_DATA_PATH."/cache/store-temp-".$order->getOrderToken().".json";

if (is_file($dataFile) && ($response = json_decode(file_get_contents($dataFile),true))) {	
	if (isset($response["x_zip"])) $order->set("x_zip",$response["x_zip"]);
	if (isset($response["x_card_num"])) {
		$response["x_card_num"] = preg_replace("/[^\d]+/","",$response["x_card_num"]);
		$response["x_card_num"] = str_repeat("*",strlen($response["x_card_num"])-4).substr($response["x_card_num"],-4);
	}
	$order->set("fullResponse",$response);
	$order->set("paymentProcessor","Authorize.net");
	if ($response["approved"]) {

		$this->setTitle("en-us|Payment Approved");
		$order->set("transactionID",$response["transaction_id"]);
		$order->set("status","ordered");
		
		$cart = new storeCart($order->id);

		$cart->updateOrder($order);	

		$order->apply();

		ob_start();
		require(SL_WEB_PATH."/inc/store/template/receipt-email.php");
		$order->sendEmail("Your ".$cfg["storeName"]." Order - #".$order->getOrderNumber(),translateHTML(ob_get_clean()));
		
		?><div class="success"><?=$response["response_reason_text"];?></div>
		<p>en-us|Thank you for your order.</p>
		<?php
	} else {
		$this->setTitle("en-us|Payment Failed");
		$order->set("status","payment-failed");
		$order->set("failure-reason",$response["response_reason_text"]);
		$order->apply();
		$this->redirect("store-payment?error");
		
	}
		
	unlink($dataFile);
} elseif (!$order->isOrdered()) {
	$this->setTitle("en-us|Payment Failed");
	$order->set("status","payment-failed");
	$order->set("failure-reason","en-us|Order already processed");
	?><div class="error">en-us|Order already processed</div><?php
}

if (isset($_GET["order"])) {
	$order = new storeOrder($_GET["order"]);
	if ($order->isValid() && $order->isOrdered()) {
		$cart = new storeCart($order->id);
		$cart->updateOrder($order);		
		
		require(SL_WEB_PATH."/inc/store/template/receipt.php");
	} else {
		if ($order->getLastErrorText()) {
			?><div class="error"><?=$order->getLastErrorText();?></div><?php
		}
	}
	
}
