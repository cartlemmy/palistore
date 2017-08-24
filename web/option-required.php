<?php

$this->setTitle("en-us|Option Required");

$this->addScript("js/itemOptions.js");
$this->addScript("js/zoomImage.js");
$this->set("zoomImage",true);
$this->setCaching(false);
$this->set("store",1);

require(SL_WEB_PATH."/inc/store/class.storeItemList.php");

$store = new store();

store::redirectCheck();

$si = new storeItemList();
$si->selectFirstOption = false;

if (setAndTrue($request["params"],"i")) {
$si->select("`nameSafe`=".slMysql::safe($request["params"]["i"]));

?>
<div class="error">en-us|You must select all options to add an item.</div>
<form action="<?=WWW_RELATIVE_BASE;?>option-required/" method="post">
<button type="submit" name="view-cart" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons cart"></span>en-us| VIEW CART</button>
</form>
<div class="cb"></div>
<?php

$si->showList(false, $request["params"]);

?>
<div class="cb"></div>
<form action="<?=WWW_RELATIVE_BASE;?>option-required/" method="post">
<button type="submit" name="view-cart" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons cart"></span>en-us| VIEW CART</button>
</form><?php
} else {
	echo "<div class='error'>en-us|You have reached this page in error.</div>";
}
