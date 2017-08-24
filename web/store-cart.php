<?php

//$this->set("content-type","sidebar");

require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/sessionOptions.php");
require(SL_WEB_PATH."/inc/cartUpdate.php");

$this->setTitle("en-us|Cart");
$this->setCaching(false);
$this->addScript("js/zoomImage.js");
$this->addScript("js/storeCart.js",'text/javascript','body-end');
$this->set("zoomImage",true);
$this->set("store",1);

if (setAndTrue($_POST,"note-to")) {
	$item = new storeItem("OI:".(int)$_POST["note-to"]);
	$item->setOI("camperNote",$_POST["note"]);
	$item->apply();
}

$store = new store();

$sc = new storeCart($store->get("cur-order",0));

$sc->checkForUpdate(array("camper","paliSession","address","deliveryType"),"cartUpdate");

foreach ($_POST as $n=>$v) {
	if (substr($n,0,10) == "_ADD_NOTE-") {
		$this->redirect(WWW_RELATIVE_BASE."store-add-note/?".substr($n,10));
	}
}
?>
<form action="<?=WWW_RELATIVE_BASE;?>store-cart/" method="post">
<?php if (!$sc->cartEmpty()) { ?>
<button type="submit" name="check-out" value="1" style="float:right" class="pali-button-small pali-blue" onclick="return storeCartValidate(event)"><span class="store-icons checkout"></span>en-us| CHECK OUT</button>
<?php } ?>
<button type="submit" name="view-store" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons continue"></span>en-us| CONTINUE SHOPPING</button>
<div class="cb"></div>
<?php

if (!$sc->showCart(false)) {
	?><div class="notify">en-us|Your cart is empty.</div><?php
}

require(SL_WEB_PATH."/inc/cartTotals.php");

?>
<div class="cb"></div>
<?php if (!$sc->cartEmpty()) { ?>
<button type="submit" name="check-out" value="1" style="float:right" class="pali-button-small pali-blue" onclick="return storeCartValidate(event)"><span class="store-icons checkout"></span>en-us| CHECK OUT</button>
<?php } ?>
<button type="submit" name="view-store" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons continue"></span>en-us| CONTINUE SHOPPING</button>
</form><?php

require(SL_WEB_PATH."/inc/cartValidate.php");


