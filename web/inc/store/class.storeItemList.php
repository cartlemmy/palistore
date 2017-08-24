<?php

require_once(realpath(dirname(__FILE__))."/class.storeItem.php");

class storeItemList {
	private $dir;
	private $selRes;
	private $cfg;
	public $selectFirstOption = false;
		
	function __construct() {
		$this->dir = realpath(dirname(__FILE__));
		$this->cfg = store::getConfig();
	}
	
	function select($where = "1") {
		if ($this->selRes = $GLOBALS["slCore"]->db->select($this->cfg["table"]["item"],"`active`=1".($this->cfg["inventoryControl"] ? " AND `quantity`>0" : "")." AND (".$where.") AND `optionParent`=0",array("orderby"=>"sortOrder"))) {
			return true;
		}
		return false;
	}
	
	function showList($templateFile = false, $info = false) {
		if ($templateFile === false) $templateFile = $this->dir."/template/item.php";
		while ($item = $this->selRes->fetch()) {
			$itemOb = new storeItem($item,$this->selectFirstOption);
			if ($itemOb->isAvailable()) {
				$item = $itemOb->get();		
				require($templateFile);
			}
		}
	}
}
