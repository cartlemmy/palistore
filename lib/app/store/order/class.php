<?php

require_once("lib/app/edit/class.php");
require_once(SL_WEB_PATH."/inc/store/class.store.php");
require_once(SL_WEB_PATH."/inc/store/class.storeOrder.php");

class slStoreOrder extends slItemEdit {
	function __construct($app) {
		$this->app = $app;
		parent::__construct($app);
	}
		
	function updateStatus($status) {
		$store = new store();
		$order = new storeOrder($this->app->args[1],true);
		$order->set("status",$status);
		$order->apply();
		return true;
	}
}
