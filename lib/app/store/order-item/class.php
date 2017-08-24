<?php

require_once("lib/app/edit/class.php");
require_once(SL_WEB_PATH."/inc/store/class.store.php");
require_once(SL_WEB_PATH."/inc/store/class.storeItem.php");
require_once(SL_WEB_PATH."/inc/store/class.storeOrder.php");

class slStoreOrderItem extends slItemEdit {
	private $item = null;
	
	function __construct($app) {
		$this->app = $app;
		parent::__construct($app);
	}
		
	function updateSpecial($update) {
		if (!isset($GLOBALS["_YP_STORE_OBJ"])) $store = new store();
		if (!$this->item) $this->item = new storeItem("OI:".$this->app->args[1]);
		
		foreach ($update as $field=>$v) {
			switch ($field) {
				case "paliSession":
					if ($res = $GLOBALS["slCore"]->db->select("db/paliSessions",array("id"=>$v))) {
						$sess = $res->fetch();
						$this->item->setOI("shipDate",$sess["startDate"]);
						$this->item->setOI("sess",$sess);
					}
					break;
					
				case "item":
					if ($res = $GLOBALS["slCore"]->db->select("db/storeItems",array("id"=>$v))) {
						$item = $res->fetch();
						$this->item->setOI("itemName",$item["name"]);
						$this->item->setOI("price",$item["price"]);
						$this->item->setOI("option",$item["option"]);
						$this->item->setOI("optionType",$item["optionType"]);
					}
					break;	
			}
			
			$this->item->setOI($field,$v);
		}
		$this->item->apply();
		return true;
	}
		
	function itemData($id) {
		$itemOb = new storeItem($id);
		$o = $itemOb->get();
		$o["subOptions"] = $itemOb->getSubOptions();		
		return $o;
	}
}
