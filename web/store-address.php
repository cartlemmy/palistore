<?php

$this->forceHTTPS();

$this->setTitle("en-us|Store Address");
//$this->set("content-type","sidebar");

$this->set("store", 1);

$this->setCaching(false);

$addrI = isset($_GET["i"]) && ((int)$_GET["i"] || $_GET["i"] === '0') ? (int)$_GET["i"] : "NEW";

require_once(SL_WEB_PATH."/inc/store/class.storeAddressBook.php");

$addr = new storeAddressBook();

$entry = $addr->getEntry($addrI);

if (setAndTrue($_POST,"continue")) {
	foreach ($_POST as $n=>$v) {
		if (in_array($n, array("continue"))) continue;
		$entry->set($n, $v);
	}
	?><script>
		var selEl = window.parent.$(<?=json_encode($_GET["el"]);?>),
			v = <?=json_encode('_ADDR:'.$entry->id);?>;
		
		function hasOption(selEl, v) {
			var i;
			for (i = 0; i < selEl[0].options.length; i++) {
				if (selEl[0].options[i].value == v) return true;
			}
			return false;
		}
		
		if (!hasOption(selEl, v)) {
			selEl.append(window.parent.$('<option>', {value:v, text:<?=json_encode($entry->getName());?>}));
		}
			
		selEl.val(v);
		
		window.parent.popupPageHide();
	</script><?php
	exit();
}

$_GET["i"] = $entry->id;

?>
<div class="header-spacer"></div>
<form action="?<?=http_build_query($_GET);?>" method="post" id="address-form" style="margin:0;max-width: none;">

<div class="form-group" style="margin:0 10px">
	<label for="name" class="control-label">Name<br><sub>(optional, for your reference)</sub></label>
	<input type="text" class="form-control" id="name" name="name" value="<?=$entry->get("name");?>">
</div>


<div class="form-group" style="margin:0 10px">
	<label for="shipAddress1" class="control-label">Address</label>
	<input type="text" class="form-control" id="shipAddress1" name="shipAddress1" value="<?=$entry->get("shipAddress1");?>">
</div>

<div class="form-group" style="margin:0 10px">
	<label for="shipAddress2" class="control-label">Address 2</label>
	<input type="text" class="form-control" id="shipAddress2" name="shipAddress2" value="<?=$entry->get("shipAddress2");?>">
</div>

<div class="no-col-small" style="width:37%;float:left">
	[!field:text,shipCity,City,<?=shortCodeParams($entry->get("shipCity"));?>]
</div>

<div class="no-col-small" style="width:38%;float:left">
	[!field:test,shipState,State,<?=shortCodeParams($entry->get("shipState"));?>]
</div>

<div class="no-col-small" style="width:25%;float:left">
	[!field:text,shipPostalCode,Zip / Postal Code,<?=shortCodeParams($entry->get("shipPostalCode"));?>]
</div>
<div style="clear:both"></div>

<button type="submit" name="continue" value="1" class="pali-button" style="float:right;margin:10px;"><?=$addrI == "NEW" ? "ADD" : "UPDATE";?> ADDRESS</button>

</form>
