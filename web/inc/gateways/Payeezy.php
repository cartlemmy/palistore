<?php

require_once(SL_INCLUDE_PATH."/payeezy.config.php");
require_once(SL_INCLUDE_PATH."/class.Payeezy.php");

$pay = new Payeezy();

$line_items = array();

foreach ($order as $item) { 
	$item["name"] = strip_tags(str_replace("<br>", "\n", $item["name"]));
	switch ($item["type"]) {
		case "section":
			$section = array(
				"name"=>$item["name"],
				"items"=>array()
			);
			break;
	
		case "item":
			if ($item["price"]) {
				$line_items[] = array(
					"description"=>$item["name"],
					"quantity"=>"1",
					"line_item_total"=>$item["price"],
					"unit_cost"=>$item["price"],
					"unit_of_measure"=>"ST",
					"commodity_code"=>"96205",
					"discount_amount"=>0,
					"discount_indicator"=>0,
					"gross_net_indicator"=>1,
					"product_code"=>"PAEN"
				);
			} else {
				$section["items"][] = $item["name"];
			}
			break;
			
		 case "subtotal":
			 if ($section && count($section["items"])) {
				 $line_items[] = array(
					"description"=>$section["name"]."\n".implode("\n", $section["items"]),
					"quantity"=>"1",
					"line_item_total"=>Payeezy::currency($item["price"]),
					"unit_cost"=>Payeezy::currency($item["price"]),
					"unit_of_measure"=>"ST",
					"commodity_code"=>"96205",
					"discount_amount"=>0,
					"discount_indicator"=>0,
					"gross_net_indicator"=>1,
					"product_code"=>"PAEN"
				);
				$section = false;
			}
			break;
	
	}	
}
	
//mail("itsupport@palimountain.com", "check?!", print_r(array($order,$line_items), true));
	
$payload = array(
	"transaction_type"=>"purchase",
	"method"=>"credit_card",
	"amount"=>Payeezy::currency($this->priceNow),
	"partial_redemption"=>"false",
	"currency_code"=>"USD",
	"credit_card"=>array(
		"cardholder_name"=>$this->infoGet("billing.billing","fname")." ".$this->infoGet("billing.billing","lname"),
		"card_number"=>$paymentInfo["ccnumber"],
		"cvv"=>$paymentInfo["vcode"],
		"exp_date"=>preg_replace('/[^\d]+/','',$paymentInfo["exp_date"])	
	),
	'billing_address' => array (
		'city' => $this->infoGet("billing.address","city"),
		'country' => $this->infoGet("billing.address","country"),
		'email' => $this->infoGet("family.family","email"),
		'phone' => array (
			'type' => 'Landline',
			'number' => $this->infoGet("family.contact","home"),
		),
		'street' => $this->infoGet("billing.address","address"),
		'state_province' => $this->infoGet("billing.address","state"),
		'zip_postal_code' => $this->infoGet("billing.address","zip")
	),
	/*'soft_descriptors' => array (
		'dba_name' => '{string}',
		'street' => '{string}',
		'city' => '{string}',
		'region' => '{string}',
		'mid' => '{string}',
		'mcc' => '{string}',
		'postal_code' => '{string}',
		'country_code' => '{string}',
		'merchant_contact_info' => '{string}',
	),
	'level2' => array (
		'tax1_amount' => '{number}',
		'tax1_number' => '{string}',
		'tax2_amount' => '{number}',
		'tax2_number' => '{string}',
		'customer_ref' => '{string}',
	),*/
	/*'level3' => array (
		//'alt_tax_amount' => '{number}',
		//'alt_tax_id' => '{string}',
		'discount_amount' => Payeezy::currency($this->get("discount")),
		'tax_amount'=>'0',
		'duty_amount' => '0',
		'freight_amount' => '0',
		//'ship_from_zip' => '{string}',
		//'ship_to_address' => array (
		//'city' => '{string}',
		//'state' => '{string}',
		//'zip' => '{string}',
		//'country' => '{string}',
		//'email' => '{string}',
		//'name' => '{string}',
		//'phone' => '{string}',
		//'address_1' => '{string}',
		'customer_number' => 'PA'.$this->get("family.uniquefamilyid"),
		'line_items' => $line_items
	)*/
);

$tempRegId = $this->getTempRegId();
$invoiceNum = "enroll-".(SL_REWRITE == "/new"?"new-":"").$tempRegId;


$paymentid = $this->applyToDB("billing.last_auth_req",array(
	"gateway"=>PAYMENT_GATEWAY,
	"tempRegId"=>$tempRegId,
	"invoiceNum"=>$proc->invoice_num,
	"gatewaySpecific"=>$gatewaySpecificReqInfo,
	"paymentHash"=>sha1($paymentInfo["exp_date"].'.'.$paymentInfo["vcode"])
));


$authResponse = $pay->request($payload);

if (isset($authResponse["transaction_status"]) && $authResponse["transaction_status"] == 'approved') {
	$paymentInfo["transaction_id"] = $authResponse["transaction_id"];
	$this->complete($paymentInfo);
	$this->endPaymentHandler($authResponse);
	return true;
}

/*if ($authResponse->response_reason_code == 11) {
	 // Duplicate transaction
	 $this->log("Duplicate transaction submitted");
	 $this->endPaymentHandler($authResponse);
	 return "!DUP";
} else {*/
	// Failed
	$this->set("status","billing-failed");

	$messages = array();
	if (isset($authResponse["message"]["transaction_status"])) $messages[] = $authResponse["message"]["transaction_status"];
	
	if (isset($authResponse["transaction_status"])) {
		switch ($authResponse["transaction_status"]) {
			case "declined":
				$messages[] = "We're sorry, the transaction was declined. Please call ".PALI_PHONE." if you need assistance.";
				break;
		}
	}
	if (isset($authResponse["message"]["error"]["Error"]["messages"])) {
		foreach ($authResponse["message"]["error"]["Error"]["messages"] as $m) {
			$messages[] = $m["description"];
		}
	} elseif (isset($authResponse["message"]["error"]["code"])) {
		$messages[] = "Gateway Error ".$authResponse["message"]["error"]["code"];
		//TODO: admin notifications
	} elseif (isset($authResponse["message"]["error"])) {
		$messages[] = $authResponse["message"]["error"];
	} elseif (isset($authResponse["bank_message"])) {
		$messages[] = "Bank message: ".$authResponse["bank_message"].(isset($authResponse["bank_resp_code"]) ? " (Bank code:".$authResponse["bank_message"].")" : "");
	}
	$message = implode("\n", $messages);
	
	$this->set("billing-error", $message);
//}

$this->endPaymentHandler($authResponse);

$id = "\n\nError ID: ".$id;

return $message.$id;
