<?php


require_once(SL_INCLUDE_PATH."/class.slWebForm.php");
$form = new slWebForm(array(
	"name"=>array("label"=>"en-us|Your Name","validate"=>"not-empty","getter"=>array($order,"get"),"setter"=>array($order,"set")),
));

?>
<form action="order-status<?=count(explode("?",$_SERVER["REQUEST_URI"])) == 2 ? "?".array_pop(explode("?",$_SERVER["REQUEST_URI"])) : "";?>" method="post" class="form-horizontal" role="form">
	<div class="form-group">
    <label for="name" class="col-sm-2 control-label">Your Name</label>
    <div class="col-sm-10">
      <input <?=$form->field("name");?> type="text" class="form-control" id="name">
      <div <?=$form->fieldMessage("name");?>></div>
    </div>
  </div>
  <div class="form-group">
    <label for="message" class="col-sm-2 control-label">Message / Question</label>
    <div class="col-sm-10">
      <textarea <?=$form->field("message");?> class="form-control" id="message" rows="10"></textarea>
      <div <?=$form->fieldMessage("message");?>></div>
    </div>
  </div>
  <button type="submit" name="send-message" value="1" class="pali-button-small pali-blue">en-us|Send Message</button>
</form>
