<?php

require_once(realpath(dirname(__FILE__))."/class.store.php");
require_once(SL_INCLUDE_PATH."/class.slValue.php");
require_once(SL_INCLUDE_PATH."/value/class.valueCurrency.php");

class storeItem {
	private $data;
	public $cartItem = false;
	private $cartItemOldValue = array();
	private $cartItemNewValue = array();
	private $initStatus = "none";
	private $dir;
	private $cfg;
	private $order = null;
	public $options = array();
	public $selectFirstOption = false;
	
	private $statusMap = array(
		"in-cart"=>"",
		"prep-ready"=>"ready",
		"deliv-ready"=>"ready"
	);

	function __construct($id,$selectFirstOption = false) {		
		$this->selectFirstOption = $selectFirstOption;
		$this->cfg = store::getConfig();
		
		if (is_array($id)) {
			$this->data = $id;
		} elseif (substr($id,0,3) == "OI:") {
			if ($GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"],array("id"=>(int)substr($id,3)))) {
				$this->cartItem = $GLOBALS["slCore"]->db->fetch($this->cfg["table"]["cart"]);
				
				//Sub Options
				$this->cartItem["subOptions"] = array();
				foreach ($this->cfg["subOptions"] as $n=>$v) {
					if (setAndTrue($this->cartItem,$n)) $this->cartItem["subOptions"][$v] = $this->cartItem[$n];
				}
		
				$this->initStatus = $this->cartItem["status"];
				if ($data = self::getItemData($this->cartItem["item"])) {
					$this->data = $data;
				} else {
					$this->data = false;
					return;
				}
			} else {
				$this->data = false;
				return;
			}
		} elseif ($data = self::getItemData($id)) {
			$this->data = $data;
		} else {
			$this->data = false;
			return;
		}
		$this->cartItemOldValue = $this->cartItem;
		$this->getOptions();
		$this->refresh();
	}
	
	public function apply($returnInfo = false) {
		ob_start();
		if ($this->cartItemNewValue) {
			echo 'update('.substr(json_encode(array(
				$this->cfg["table"]["cart"],$this->cartItemNewValue,array("id"=>$this->cartItem["id"])
			), JSON_PRETTY_PRINT), 1, -1).')'."\n";
			$GLOBALS["slCore"]->db->update($this->cfg["table"]["cart"],$this->cartItemNewValue,array("id"=>$this->cartItem["id"]));
		}
		
		$notified = false;
		if ($this->initStatus != $this->cartItem["status"]) {
			//echo "Order Item status changed from ".$this->initStatus." to ".$this->cartItem["status"]." (#".$this->cartItem["id"]." ".$this->data["name"].")\n";
			store::notify("item-status.".$this->cartItem["status"],$this);
			$notified = true;
		}
		
		if ($this->initStatus != "in-cart" && $this->initStatus != "none") {
			$changed = array("item"=>$this,"from"=>array(),"to"=>array());

			foreach ($this->cartItemNewValue as $n=>$v) {
				if (!isset($this->cartItemOldValue[$n]) || $this->cartItemOldValue[$n] != $this->cartItemNewValue[$n]) {
					$changed["from"][$n] = isset($this->cartItemOldValue[$n]) ? $this->cartItemOldValue[$n] : false;
					$changed["to"][$n] = $v;
				}
			}
				
			if ($changed && !$notified) store::notify("item-changed",$changed);
		}

		$out = ob_get_clean();			
		return $returnInfo ? $out : true;		
	}
	
	public static function getItemData($item) {
		$cfg = store::getConfig();
		if ($GLOBALS["slCore"]->db->select($cfg["table"]["item"],is_numeric($item) ? array("id"=>$item) : array("nameSafe"=>$item))) {
			return $GLOBALS["slCore"]->db->fetch($cfg["table"]["item"]);
		}
		return false;
	}
	
	public static function getItemID($item) {
		if ($item = self::getItemData($item)) {
			return $item["id"];
		}
		return false;
	}
	
	function refresh() {
		if (!(isset($this->data["namePlural"]) && $this->data["namePlural"])) {
			$this->data["namePlural"] = $this->data["name"].(substr($this->data["name"],-1) == "s" ? "" : "s");
		}
		
		// Image
		$this->data["imageSrc"] = WWW_RELATIVE_BASE."web/store-img/".$this->data["nameSafe"];
		$this->data["imageExt"] = array_pop(explode(".",array_shift(explode(";",$this->data["image"],2))));
		
		// Price
		$val = new slValue("db/storeItems","price");
		$val->value = $this->data["price"];
		$val->update();
		$this->data["priceFormatted"] = $val->toString();
		$this->data["priceLocal"] = $val->getFloat();
		
		//Quantity
		$oldAQ = $this->data["adjustedQuantity"];
		$this->data["adjustedQuantity"] = $this->data["quantity"] - $this->getTotalInCarts();
		if ($oldAQ != $this->data["adjustedQuantity"]) {
			$this->update("adjustedQuantity",$this->data["adjustedQuantity"]);
		}
		
		//Description
		$desc = explode("\n",$this->data["description"]."\n");
		$inUL = false;
		
		$match = array();
		foreach ($desc as &$line) {
			if (substr($line,0,1) == "!") {
				$line = "<span class=\"urgent\">".trim(substr($line,1))."</span>";
			} elseif (preg_match('/^\.([\w\-\d]+)\:(.*)/',$line,$match)) {
				$line = "<div class=\"".$match[1]."\">".$match[2]."</div>";
			}
			
			if (substr($line,0,1) == "*") {
				if (!$inUL) {
					$line = "<ul><li>".trim(substr($line,1))."</li>";
					$inUL = true;
				} else {
					$line = "<li>".trim(substr($line,1))."</li>";
				}
			} elseif ($inUL) {
				$line = "</ul>\n".$line;
			}
		}
		 
		$desc = str_replace(
			array(">\n","\n","\t"),
			array(">","<br />"," &nbsp;&nbsp "),
			implode("\n",$desc)
		);
		
		
		$this->data["descriptionHTML"] = $desc;
		
		//Options
		$this->data["options"] = array();
		if ($this->options) {
			$firstOpt = true;
			foreach ($this->options as $option) {
				$optionData = $option->get();
				unset($optionData["image"]);
				$optionData["subOptions"] = $option->getSubOptions();
				$n = valueToString($optionData["optionType"],$this->cfg["table"]["item"]."/optionType");
				if (!isset($this->data["options"][$n])) {
					if ($this->selectFirstOption) {
						$this->data["options"][$n] = array();
					} else {
						$od = $this->get();
						unset($od["image"]);
						$od["subOptions"] = $this->getSubOptions();
						$od["option"] = "Select One...";
						$this->data["options"][$n] = array($od);
					}
				}
				$this->data["options"][$n][] = $optionData;				
				if ($firstOpt && $this->selectFirstOption) { //Select first option
					$copy = array("description","imageSrc","imageExt","priceFormatted","priceLocal","adjustedQuantity","quantity");
					foreach ($copy as $n) {
						$this->data[$n] = $optionData[$n];
					}
					$firstOpt = false;
				}
			}
		}
	}
	
	function update($n,$v) {
		$GLOBALS["slCore"]->db->update($this->cfg["table"]["item"],array($n=>$v),array("id"=>$this->data["id"]));
	}
	
	function getTotalInCarts() {
		$cnt = 0;
		$this->data["cartQuantity"] = 0;
		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"],array("item"=>$this->data["id"],"session"=>session_id(),"orderId"=>isset($this->data["orderId"])?$this->data["orderId"]:0))) {
			while ($item = $GLOBALS["slCore"]->db->fetch($this->cfg["table"]["cart"], $res)) {
				if (time() > $item["expires"] && $item["status"] == "in-cart") {
					$GLOBALS["slCore"]->db->delete($this->cfg["table"]["cart"],array("id"=>$item["id"]));
				} else {
					if ($item["session"] == session_id()) $this->data["cartQuantity"]++;
					$cnt ++;
				}
			}
		}
		return $cnt;
	}
	
	function isAvailable() {
		if (!$this->data["active"]) return false;
		if ($this->cfg["inventoryControl"] && $this->data["adjustedQuantity"] <= 0) return false;
		return true;
	}
	
	public function set($n,$v) {
		$this->data[$n] = $v;
	}
	
	public function get($n = false) {
		if ($n === false)	return $this->data;
		return isset($this->data[$n]) ? $this->data[$n] : null;
	}
	
	public function setOI($n,$v) {
		$this->cartItemNewValue[$n] = $v;
		$this->cartItem[$n] = $v;
	}
	
	public function getOI($n = false) {
		if ($n === false)	return $this->cartItem;
		return isset($this->cartItem[$n]) ? $this->cartItem[$n] : null;
	}
	
	function getOptions() {
		if ($this->data["optionParent"]) return;
		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["item"],"`active`=1 AND `optionParent`=".$this->data["id"])) {
			while ($row = $res->fetch()) {
				$itemOb = new storeItem($row);
				if ($itemOb->isAvailable()) $this->options[] = $itemOb;
			}
		}
	}
	
	public function getSubOptions() {
		if ($opts = $this->get("subOptions")) {
			$opts = explode("\n",$opts);
			
			$rv = array();
			
			foreach ($opts as $o) {
				$o = explode(":",$o,2);
				$o[0] = trim($o[0]);
				
				$options = explode(",",$o[1]);
				$safeName = safeName($o[0]);
				
				$oi = array(
					"label"=>$o[0],
					"safeName"=>$safeName,
					"field"=>toCamelCase("opt-".$o[0]),
					"n"=>"subOption-".$this->get("id")."-".$safeName,
					"opts"=>$this->selectFirstOption ? array() : array("0"=>"Select One...")
				);
				
				foreach ($options as $option) {
					$oi["opts"][trim($option)] = trim($option);
				}
				
				$rv[] = $oi;
			}
			return $rv;
		}
		return false;
	}
}
