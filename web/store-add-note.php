<?php

//$this->set("content-type","sidebar");

require(SL_WEB_PATH."/inc/store/class.storeItem.php");
require(SL_WEB_PATH."/inc/sessionOptions.php");
require(SL_WEB_PATH."/inc/cartUpdate.php");

$this->setTitle("en-us|Add Note");
$this->setCaching(false);
$this->set("store",1);

$item = new storeItem("OI:".(int)$request["rawParams"]);

?>
<form action="<?=WWW_RELATIVE_BASE?>store-cart/" method="post">
<h3>Note for <?=$item->getOI("camper")?$item->getOI("camper"):"Your Camper";?></h3>
<input type="hidden" name="note-to" value="<?=(int)$request["rawParams"];?>">
<textarea name="note" style="padding:0.1in;width:3.8in;height:3.8in;font-size:14pt;font-family:store-note;overflow:hidden;"><?=htmlspecialchars($item->getOI("camperNote"));?></textarea>
<div class="cb"></div>
<button type="submit" name="update-note" value="1" class="pali-button-small pali-blue"><span class="store-icons continue"></span>en-us| SAVE NOTE</button>
</form><?php


