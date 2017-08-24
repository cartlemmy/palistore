<?php

require_once(SL_WEB_PATH.'/inc/store/anet_php_sdk/AuthorizeNet.php');

$proc = new AuthorizeNetAIM;
$proc->amount = $this->priceNow;
$proc->card_num = $paymentInfo["ccnumber"];
$proc->exp_date = $paymentInfo["exp_date"];
$proc->card_code = $paymentInfo["vcode"];
$proc->description = 'Pali Adventures Online Enrollment Payment';

$proc->duplicate_window = 10 * 60;

$proc->first_name = $this->infoGet("billing.billing","fname");
$proc->last_name = $this->infoGet("billing.billing","lname");

//$proc->email = $this->infoGet("family.family","email"); // Commented out because we don't want to send E-mail from Authorize.net
//$proc->email = "itsupport@palimountain.com";

$proc->phone = $this->infoGet("family.contact","home");

$proc->address = $this->infoGet("billing.address","address");
$proc->city = $this->infoGet("billing.address","city");
$proc->state = $this->infoGet("billing.address","state");
$proc->zip = $this->infoGet("billing.address","zip");

$tempRegId = $this->getTempRegId();
$invoiceNum = "enroll-".(SL_REWRITE == "/new"?"new-":"").$tempRegId;
$proc->invoice_num = $invoiceNum;

$paymentid = $this->applyToDB("billing.last_auth_req",array(
	"gateway"=>PAYMENT_GATEWAY,
	"tempRegId"=>$tempRegId,
	"invoiceNum"=>$proc->invoice_num,
	"gatewaySpecific"=>$gatewaySpecificReqInfo,
	"paymentHash"=>sha1($paymentInfo["exp_date"].'.'.$paymentInfo["vcode"])
));

$authResponse = $proc->authorizeAndCapture();

if ($authResponse->approved) {
	$paymentInfo["transaction_id"] = $authResponse->transaction_id;
	$this->complete($paymentInfo);
	$this->endPaymentHandler($authResponse);
	return true;
}

if ($authResponse->response_reason_code == 11) {
	 // Duplicate transaction
	 $this->log("Duplicate transaction submitted");
	 $this->endPaymentHandler($authResponse);
	 return "!DUP";
} else {
	// Failed
	$this->set("status","billing-failed");
	$this->set("billing-error",$authResponse->response_reason_text);
}

$this->endPaymentHandler($authResponse);
$id = "\n\nError ID: ".$id;

$res = $authResponse->response_reason_text;
if (!$res) $res = ($authResponse->error_message ? $authResponse->error_message : "Unkown Error").$id;

return $res.$id;
