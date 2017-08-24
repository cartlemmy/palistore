<?php

//$this->set("content-type","sidebar");

require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/sessionOptions.php");

$this->setCaching(false);
$this->set("store",1);

$store = new store();
$sc = new storeCart($store->get("cur-order",0));

if ($item = $sc->add($request["rawParams"])) {
	$this->setTitle(format(translate("en-us|%% Added to Cart"),$item["name"]));
	$this->addScript("js/zoomImage.js");
	$this->addScript("js/storeCart.js",'text/javascript','body-end');
	$this->set("zoomImage",true);
	
	require(SL_WEB_PATH."/inc/store/template/item-added.php");
	
	?><h3>en-us|Your Cart</h3>
	<form action="<?=WWW_RELATIVE_BASE;?>store-cart/" method="post">
	<?php if (!$sc->cartEmpty()) { ?>
	<button type="submit" name="check-out" value="1" style="float:right" class="pali-button-small pali-blue" onclick="return storeCartValidate(event)"><span class="store-icons checkout"></span>en-us| CHECK OUT</button>
	<?php } ?>
	<button type="submit" name="view-store" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons continue"></span>en-us| CONTINUE SHOPPING</button>
	<div class="cb"></div>
<?php
	
	$sc->showCart();
	
	require(SL_WEB_PATH."/inc/cartTotals.php");
	
	?>
	<div class="cb"></div>
	<?php if (!$sc->cartEmpty()) { ?>
	<button type="submit" name="check-out" value="1" style="float:right" class="pali-button-small pali-blue" onclick="return storeCartValidate(event)"><span class="store-icons checkout"></span>en-us| CHECK OUT</button>
	<?php } ?>
	<button type="submit" name="view-store" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons continue"></span>en-us| CONTINUE SHOPPING</button>
	</form><?php
	
	require(SL_WEB_PATH."/inc/cartValidate.php");
	
} else {
	$item = $sc->item ? $sc->item->get() : array();
		
	echo "<button onclick=\"window.location.href='".$store->get("previousPage")."'\">en-us|&larr; BACK</button>";
	
	echo "<div class=\"error\">";
	switch ($sc->getLastErrorText()) {
		case "no-option":
			$_POST["si"] = $item["id"];
			$this->redirect(WWW_RELATIVE_BASE."option-required/?".slRequestInfo::encodeGet($_POST));
			break;
			
		case "unavailable":
			echo format(translate("en-us|%% is currently unavailable."), $item["name"]);
			break;
		
		case "out-of-stock":
			echo format(translate("en-us|%% is currently out of stock."), $item["name"]);
			break;
		
		case "not-found":
			echo format(translate("en-us|Item '%%' not found."), $request["rawParams"]);
			break;
		
		case "not-specified":
			echo format(translate("en-us|Item not specified."), $request["rawParams"]);
			break;
					
		default:
			echo format(translate("en-us|Error: %%"), $sc->getLastErrorText());
			break;
	}
	echo "</div>";
}

