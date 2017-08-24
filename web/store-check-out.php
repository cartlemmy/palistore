<?php

$this->forceHTTPS();

require(SL_WEB_PATH."/inc/store/class.storeCart.php");
require(SL_WEB_PATH."/inc/store/class.storeOrder.php");
require(SL_WEB_PATH."/inc/sessionOptions.php");
require(SL_WEB_PATH."/inc/cartUpdate.php");
require_once(SL_INCLUDE_PATH."/class.slWebForm.php");

$this->setTitle("en-us|Checkout");
$this->setCaching(false);
$this->set("store",1);

$store = new store();

store::redirectCheck();

$order = new storeOrder($store);

$form = new slWebForm(array(
	"name"=>array("label"=>"en-us|Your Name","validate"=>"not-empty","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"email"=>array("type"=>"email","label"=>"en-us|Your E-mail","validate"=>"not-empty,email","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"phone"=>array("label"=>"en-us|Your Phone #","validate"=>"not-empty","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"parentName"=>array("label"=>"en-us|Parent Name","validate"=>"not-empty","getter"=>array($order,"get"),"setter"=>array($order,"set")),
	"isParent"=>array("getter"=>array($order,"get"),"setter"=>array($order,"set"))
));

if ($form->submitted()) {
	$order->update();
	$order->apply();
	$this->redirect(WWW_RELATIVE_BASE."store-payment/");
}

$form->attachToWeb($this);

?><script type="text/javascript">
var updateParentNameTimer = null,
	oldParentName = <?=json_encode($order->get("parentName",""));?>,
	isParent = <?=$order->get("isParent")?"true":"false";?>;

function updateParentName() {
	if (updateParentNameTimer) clearTimeout(updateParentNameTimer);
	updateParentNameTimer = setTimeout(function(){
		if (isParent == false && document.getElementById('isParent').checked) oldParentName = document.getElementById('parentName').value;
		isParent = document.getElementById('isParent').checked;
		
		if (isParent) {
			if (document.getElementById('parentName').slSpecial) {
				document.getElementById('parentName').slSpecial.setValue(document.getElementById('name').value);
			} else {
				document.getElementById('parentName').value = document.getElementById('name').value;
			}
		}
	},100);
}
</script>
<?php if ($order->hasItems()) { ?>
<form action="<?=WWW_RELATIVE_BASE;?>store-check-out/" method="post" class="form-horizontal" role="form">
	<div class="form-group">
    <label for="name" class="col-sm-2 control-label">Your Name</label>
    <div class="col-sm-10">
      <input <?=$form->field("name");?> type="text" class="form-control" id="name" onkeyup="updateParentName()" onchange="updateParentName()">
      <div <?=$form->fieldMessage("name");?>></div>
    </div>
  </div>
	
  <div class="form-group">
    <label for="email" class="col-sm-2 control-label">Your E-mail</label>
    <div class="col-sm-10">
      <input <?=$form->field("email");?> type="email" class="form-control" id="email">
      <div <?=$form->fieldMessage("email");?>></div>
    </div>
  </div>
  
  <div class="form-group">
    <label for="phone" class="col-sm-2 control-label">Your Phone #</label>
    <div class="col-sm-10">
      <input <?=$form->field("phone");?> type="phone" class="form-control" id="phone">
      <div <?=$form->fieldMessage("phone");?>></div>
    </div>
  </div>
  
  <div class="form-group">
    <label for="parentName" class="col-sm-2 control-label">Parent Name</label>
    <div class="col-sm-10">
      <input <?=$form->field("parentName");?> type="text" class="form-control" id="parentName" name="parentName">
      <div <?=$form->fieldMessage("parentName");?>></div>
      <label><input <?=$form->field("isParent");?> type="checkbox" value="1" id="isParent" onchange="updateParentName()"> I am the parent</label>
    </div>
  </div>

	<div class="alert alert-info"><?php $this->showPageContent("store-disclaimer"); ?></div>
	  
  <div class="col-sm-12">
		<div class="form-message" style="float:right"></div>
		<div style="clear:both"></div>
		<button type="submit" name="view-cart" value="1" style="float:left" class="pali-button-small pali-blue"><span class="store-icons larr"></span>en-us| Back to Cart</button>
		<button <?=$form->submit();?> type="submit" class="pali-button-small pali-blue" style="float:right">en-us|CONTINUE <span class="store-icons rarr"></span></button>
	</div>	

</form>

<?php } else { ?>
	<form action="<?=WWW_RELATIVE_BASE;?>store-check-out/" method="post" class="form-horizontal" role="form">
	 <div class="col-sm-12">
		<div class="error">en-us|You cannot check out an empty cart.</div>
		<div style="clear:both"></div>
		<button type="submit" name="view-store" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons continue"></span>en-us| CONTINUE SHOPPING</button>
	</div>	
<?php } ?>
