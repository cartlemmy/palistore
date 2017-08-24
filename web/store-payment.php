<?php

$this->forceHTTPS();

require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/store/class.storeOrder.php");
require(SL_WEB_PATH."/inc/cartUpdate.php");
require_once(SL_INCLUDE_PATH."/class.slWebForm.php");

require_once(SL_WEB_PATH.'/inc/store/anet_php_sdk/AuthorizeNet.php');

$this->setTitle("en-us|Payment Info");
$this->setCaching(false);
$this->set("store",1);

$store = new store();

store::redirectCheck();

$order = new storeOrder($store);

$cfg = store::getConfig();

if ($order->hasItems()) { 
	
$name = explode(" ",trim($order->get("name")));
if (count($name)) $order->set("x_first_name",array_shift($name));
if (count($name)) $order->set("x_last_name",array_pop($name));

$form = new slWebForm(array(
	"x_card_num"=>array("label"=>"en-us|Credit Card Number","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"x_expiration"=>array("label"=>"en-us|Expiration","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"x_card_code"=>array("label"=>"en-us|Expiration","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"x_first_name"=>array("label"=>"en-us|First Name","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"x_last_name"=>array("label"=>"en-us|Last Name","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"x_zip"=>array("label"=>"en-us|Expiration","getter"=>array($order,"get"),"setter"=>array($order,"set"))
));

if ($form->submitted()) {
	$order->update();
	$order->apply();
	$this->redirect(WWW_RELATIVE_BASE."store-payment/");
}

$time = time();
$fp = AuthorizeNetDPM::getFingerprint($cfg["authorizeNet"]["APILoginID"], $cfg["authorizeNet"]["transactionKey"], $order->getTotal("USD"), $order->get("id"), $time);
$sim = new AuthorizeNetSIM_Form(
		array(
			'x_amount'        => $order->getTotal("USD"),
			'x_fp_sequence'   => $order->get("id"),
			'x_invoice_num'   => $order->getOrderToken(),
			'x_fp_hash'       => $fp,
			'x_fp_timestamp'  => $time,
			'x_relay_response'=> "TRUE",
			'x_duplicate_window' => 240,
			'x_relay_url'     => WWW_BASE."an-relay.php",
			'x_login'         => $cfg["authorizeNet"]["APILoginID"],
			'x_email'         => $order->get("email"),
			'x_phone'         => $order->get("phone")
		)
);

$hidden_fields = $sim->getHiddenFieldString();
switch ($cfg["testMode"]) {
	case "sandbox":
		$post_url = AuthorizeNetDPM::SANDBOX_URL;
		break;
	
	case "local":
		$post_url = "payment-test";
		break;
		
	default:
		$post_url = AuthorizeNetDPM::LIVE_URL;
		break;
}

$form->attachToWeb($this);

?><script type="text/javascript">
	function ccFormat(el) {
		var v = el.value.replace(/[^\d]+/gi,''), frmt = [];
		for (var i = 0; i < v.length; i += 4) {
			frmt.push(v.substr(i,4));
		}
		 el.value = frmt.join(" ");
	}
	
	function expFormat(el) {
		var v = el.value.replace(/[^\d]+/gi,'').substr(0,4);
		if (v.length < 4) return;
		el.value = v.substr(0,2)+"/"+v.substr(2,2);
	}
</script>
<h1><?=$this->getTitle();?></h1>
<?php

if ($request["rawParams"] == "error") {
	?><div class="error"><?=$order->get("failure-reason");?> Please enter payment info and try again.</div><?php
}

?>
<form method="post" action="<?=$post_url;?>">
		<?=$hidden_fields;?>
		<fieldset>
			<div class="form-group col-sm-6">
			<label for="name">CC Number <div class="cc-logo visa"></div><div class="cc-logo mastercard"></div><div class="cc-logo amex"></div></label>
				<input <?=$form->field("x_card_num");?> type="text" class="form-control" onblur="ccFormat(this)">
			</div>
			<div class="form-group col-sm-3">
				<label>Expiration (MM/YY)</label>
				<input <?=$form->field("x_exp_date");?> type="text" class="form-control" onblur="expFormat(this)"></input>
			</div>
			<div class="form-group col-sm-3">
				<label>CCV</label>
				<input <?=$form->field("x_card_code");?> type="text" class="form-control"></input>
			</div>
		</fieldset>
		<fieldset>
				<div class="form-group col-sm-5">
						<label>First Name</label>
						<input <?=$form->field("x_first_name");?> type="text" class="form-control"></input>
				</div>
				<div class="form-group col-sm-5">
						<label>Last Name</label>
						<input <?=$form->field("x_last_name");?> type="text" class="form-control"></input>
				</div>
				<div class="form-group col-sm-2">
						<label>Zip Code</label>
						<input <?=$form->field("x_zip");?> type="text" class="form-control"></input>
				</div>
		</fieldset>
		<div>
			<button type="button" onclick="window.location.href='<?=WWW_RELATIVE_BASE;?>store-check-out/'" value="1" style="float:left" class="pali-button-small pali-blue"><span class="store-icons larr"></span>en-us| Back</button>		
			<button type="submit" class="pali-button-small pali-green" style="float:right">BUY <span class="store-icons rarr"></span></button>
		</div>
</form>
<?php } else { ?>
	<form action="<?=$_SERVER["REQUEST_URI"];?>" method="post" class="form-horizontal" role="form">
	 <div class="col-sm-12">
		<div class="error">en-us|You cannot check out an empty cart.</div>
		<div style="clear:both"></div>
		<button type="submit" name="view-store" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons continue"></span>en-us| CONTINUE SHOPPING</button>
	</div>
	</form>
<?php } ?>
