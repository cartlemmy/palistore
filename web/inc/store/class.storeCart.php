<?php

require_once(SL_INCLUDE_PATH."/class.slValue.php");
require_once(SL_INCLUDE_PATH."/value/class.valueCurrency.php");
require_once(realpath(dirname(__FILE__))."/class.storeItem.php");
require_once(realpath(dirname(__FILE__))."/class.storeTax.php");

class storeCart extends slClass {
	public $dir;
	private $cfg;
	public $subTotal;
	public $taxTotal;
	public $shippingAndHandling;
	public $total;
	public $cartWhere;
	private $orderId;
	public $item = null;
	public $session_id;
	public $totals;
	public $items = array();
	private $addOnUpdate = array("camper","paliSchool","expires","schoolName","shipDate");
	private $ssValidateFile = false;
	private $wasValidated = false;
	private $cartErrors = array();
	
	function __construct($orderId = 0, $session_id = false) {
		$this->session_id = $session_id === false ? session_id() : $session_id;
		$this->dir = realpath(dirname(__FILE__));
		$this->cartWhere = $session_id === true ? array("orderId"=>(int)$orderId) : (!$orderId ? array("session"=>$this->session_id) : array("orderId"=>(int)$orderId, "session"=>$this->session_id));
		$this->orderId = (int)$orderId;
		$this->cfg = store::getConfig();
		if (isset($this->cfg["ssValidateFile"])) $this->serverSideValidate($this->cfg["ssValidateFile"]);
		$this->taxCalc = new storeTax();
		$this->subTotal = new slValue("db/storeOrderItems","price");
		$this->taxTotal = new slValue("db/storeOrderItems","price");
		$this->shippingAndHandling = new slValue("db/storeOrderItems","price");
		$this->total = new slValue("db/storeOrderItems","price");
		store::DBGCLASS($this->orderId);
	}
	
	private function serverSideValidate($includeFile) {
		if (is_file($includeFile)) {
			$this->ssValidateFile = $includeFile;
		}
	}
	
	function checkForUpdate($extraFields = array(),$updateFunction = false) {
		$fields = array_merge(array("qty","address"),$extraFields);
		if (isset($_POST) && is_array($_POST)) {
			$update = array();

			foreach ($_POST as $n=>$v) {
				if (substr($n,0,8) == "_REMOVE-") {
					$this->delete(substr($n,8));
				} else {
					foreach ($fields as $field) {
						if (substr($n,0,strlen($field) + 1) == $field."-") {
							$id = substr($n,strlen($field) + 1);
							if (!isset($update[$id])) $update[$id] = array();
							$update[$id][$field] = $v;
						}
					}
				}
			}
			
			if (count($update) && $this->ssValidateFile) {
				$itemsToValidate = array();
				foreach ($update as $id=>$data) {
					if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"], array("id"=>(int)$id))) {
						$old = $res->fetch();
					} else $old = $data;
					$val = array();
					foreach ($old as $n=>$v) {
						$val[$n] = array("old"=>$v,"new"=>isset($data[$n]) ? $data[$n] : $v);
					}
					$itemsToValidate[$id] = $val;
				}
				
				
				$validateItems = array(
					"where"=>"pre-checkout",
					"cart"=>$itemsToValidate
				);
				
				$this->cartErrors = array();
				$this->wasValidated = true;
				require($this->ssValidateFile);
			}
			
			store::DBG('$update ', $update);
			
			foreach ($update as $id=>$data) {
				if (!(int)$id) continue;
				$data["id"] = (int)$id;
				$data["expires"] = time() + $this->cfg["cartItemLiveSeconds"];
				
				if ($updateFunction) call_user_func_array($updateFunction,array(&$data));
				unset($data["id"]);
				store::DBG($GLOBALS["slCore"]->db->update($this->cfg["table"]["cart"], $data, array("id"=>(int)$id), array("returnQuery"=>1))); 
				$GLOBALS["slCore"]->db->update($this->cfg["table"]["cart"], $data, array("id"=>(int)$id));
				
				$addOnData = array();
				foreach ($data as $n=>$v) {
					if (in_array($n,$this->addOnUpdate)) $addOnData[$n] = $v;
				}
				store::DBG('$addOnData ', $addOnData);
				$GLOBALS["slCore"]->db->update($this->cfg["table"]["cart"], $addOnData, array("addonParent"=>(int)$id));
			}

			store::redirectCheck();
			
			if (!$this->hasErrors()) store::redirectCheck();
		}
	}
	
	public function hasErrors() {
		return count($this->cartErrors);
	}
	
	public function cartError($itemId, $field, $message) {
		if (!isset($this->cartErrors[$itemId])) $this->cartErrors[$itemId] = array();
		if (!isset($this->cartErrors[$itemId][$field])) $this->cartErrors[$itemId][$field] = array();
		$this->cartErrors[$itemId][$field][] = $message;		
	}
	
	function add($item, $addon = 0, $extra = array()) {
		// TODO: return if order is complete
		store::DBG('add', $item);
		if ($item == '') return $this->error("not-specified");
		
		if ($itemData = storeItem::getItemData($item)) {
			$this->item = new storeItem($itemData);
			$itemData = $this->item->get();
			
			if (setAndTrue($itemData,"options") && $itemData["optionParent"] == 0 && store::optCount($itemData["options"]) != 1) {
				return $this->error("no-option");
			}
	
			if (!$this->item->get("active")) return $this->error("inactive");
			if ($this->cfg["inventoryControl"] && $this->item->get("adjustedQuantity") <= 0) return $this->error("out-of-stock");
			
			
			$data = array_merge(
				$GLOBALS["_YP_STORE_OBJ"]->getDefaults("storeOrderItems"),
				array(
				"session"=>session_id(),
				"item"=>$itemData["id"],
				"itemName"=>$itemData["name"],
				"addonParent"=>$addon,
				"price"=>$itemData["price"],
				"option"=>$itemData["option"],
				"optionType"=>$itemData["optionType"],
				"status"=>"in-cart",
				"added"=>time(),
				"expires"=>time() + $this->cfg["cartItemLiveSeconds"],
				"orderId"=>$this->orderId
			));
			
			if ($opts = $this->item->getSubOptions()) {
				foreach ($opts as $o) {
					if (isset($extra["subOption"][$o["safeName"]])) {
						$data[$o["field"]] = $extra["subOption"][$o["safeName"]];
					} elseif (isset($_POST[$o["n"]]) && in_array($_POST[$o["n"]],$o["opts"])) {
						$data[$o["field"]] = $_POST[$o["n"]];
					} else return $this->error("no-option");
				}
			}
			
			foreach ($data as $n=>$v) {
				$this->item->set($n,$v);
			}
			
			/*if ($this->ssValidateFile) {
				$validateItems = &$data;
				require($this->ssValidateFile);
			}*/
			
			$id = $GLOBALS["slCore"]->db->insert($this->cfg["table"]["cart"],$data);
			
			$this->item->refresh();

			$itemData = $this->item->get();
			$itemData["insertedId"] = $id;
		} else return $this->error("not-found");
		$itemData["cnt"] = 1;
		return $itemData;
	}
			
	function getCartItems() {
		$this->items = array();
		
		$this->subTotal->value = 0;
		$this->subTotal->update();
		
		$this->shippingAndHandling->value = 0;
		$this->shippingAndHandling->update();
		
		$this->taxTotal->value = 0;
		$this->taxTotal->update();
		
		$items = array();
		$this->totals = array();
		
		$updateCartShipping = false;

		if ($GLOBALS["_YP_STORE_OBJ"]->getUserData("update-cart-defaults")) {
			$GLOBALS["_YP_STORE_OBJ"]->set("update-cart-defaults", false);
			$updateCartDefaults = $GLOBALS["_YP_STORE_OBJ"]->getDefaults("storeOrderItems");
		}

		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"],$this->cartWhere,array("orderby"=>"id DESC"))) {
	
			while ($item = $res->fetch()) {
				$val = new slValue("db/storeItems","price");
				$val->value = $item["price"];
				$val->update();

				if ($r2 = $GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"],array("addonParent"=>$item["id"]))) {
					while ($s = $r2->fetch()) {
						$val->add($s["price"]);
					}
				}
				
				$item["priceWithAddonsFormatted"] = $val->toString();
				$item["priceWithAddonsUSD"] = $val->getFloat();
				
				$this->items[] = $item;
				
				$upd = array("expires"=>time() + $this->cfg["cartItemLiveSeconds"]);
				
				if ($updateCartDefaults && !$item["customized"]) {
					$item = $updateCartDefaults + $item;
					foreach ($updateCartDefaults as $n=>$v) {
						if (!setAndTrue($item,$n)) $upd[$n] = $v;
					}
				}
				
				if (!$item["noTax"] && !setAndTrue($this->cfg,"noTax")) {
					$rate = $this->taxCalc->getTaxRate($item["shipPostalCode"], $item["shipCountry"]);
					$tax = new slValue("db/storeOrderItems","price");
					$tax->value = $item["price"];
					$tax->multiply($rate);
					$item["taxApplied"] = $upd["taxApplied"] = $tax->toString();
					
					$this->taxTotal->add($tax->value);
				}				
				
				$this->update($upd, $item["id"]);
				$this->subTotal->add($item["price"]);
				$items[] = $item["id"];
			}
		}
		
		$this->subTotal->round();
		$this->taxTotal->round();
				
		if (setAndTrue($this->cfg,"shippingTable")) {
			foreach ($this->cfg["shippingTable"] as $table) {
				$var = $table["var"];
				unset($table["var"]);
				foreach ($table as $n=>$row) {
					if ($row["where"] == "ELSE") {
						$pass = true;
					} else {
						try {
							if (@eval('$pass = '.$row["where"].';') === false) $pass = false;
						} catch (Exception $e) {
							//echo '$pass = '.$row["where"].';'."\n".$e->getMessage();
							$pass = false;
						}
					}
					//echo '$pass = '.$row["where"].';'."\n";
					if ($pass) {
						for ($i = 0; $i < count($row["do"]); $i += 2) {
							$action = $row["do"][$i];
							$v = $row["do"][$i + 1];
							switch ($action) {
								case "NEXT":
									continue 4;
									
								default:
									eval($var.'->'.strtolower($action).'('.$v.');');
									break;
							}
						}
					}
				}				
			}
		}
		
		$this->shippingAndHandling->round();
		
		//TODO: Check for discounts / coupons
		
		$this->total->value = $this->subTotal->value;
		$this->total->update();
		$this->total->add($this->taxTotal);
		$this->total->add($this->shippingAndHandling);
		
		if ($this->taxTotal->getFloat() || $this->shippingAndHandling->getFloat()) {
			$this->totals[] = array(
				"name"=>"en-us|Subtotal",
				"value"=>$this->subTotal->toString()
			);	
		}
		
		if ($this->taxTotal->getFloat()) {		
			$this->totals[] = array(
				"name"=>"en-us|Tax",
				"value"=>$this->taxTotal->toString()
			);
		}
			
		if ($this->shippingAndHandling->getFloat()) {		
			$this->totals[] = array(
				"name"=>"en-us|Shipping and Handling",
				"value"=>$this->shippingAndHandling->toString()
			);
		}
		
		$this->totals[] = array(
			"name"=>"en-us|Total",
			"value"=>$this->total->toString()
		);
			
		return $items;
	}
	
	private function totalOf($where) {
		$tot = new slValue("db/storeOrderItems","price");
		
		foreach ($this->items as $item) {
			foreach ($item as $n=>$v) {
				$$n = $v;
			}
			$pass = false;
			//echo '$pass = '.$where.';'."\n";
			try {
				if (eval('$pass = '.$where.';') === false) $pass = false;
			} catch (Exception $e) {
				//echo '$pass = '.$where.';'."\n".$e->getMessage();
				$pass = false;
			}
			if ($pass) {
				//echo '$tot->add('.$item["price"].');';
				$tot->add($item["price"]);
			}
		}
		
		return $tot;
	}
	
	function update($data,$id) {
		$GLOBALS["slCore"]->db->update($this->cfg["table"]["cart"], $data, array("id"=>(int)$id, "session"=>$this->session_id));
	}
	
	function delete($id) {
		$GLOBALS["slCore"]->db->delete($this->cfg["table"]["cart"],array("id"=>(int)$id, "session"=>$this->session_id));
	}
	
	function cartEmpty() {
		return $this->cartItemCount() == 0;
	}
	
	function cartItemCount() {
		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"],$this->cartWhere,array("select"=>"COUNT(`id`) AS 'cnt'"))) {
			$row = $GLOBALS["slCore"]->db->fetch($this->cfg["table"]["cart"], $res);
			return (int)$row["cnt"];
		}
		return 0;
	}
	
	function updateOrder($order) {
		$items = $this->getCartItems();
		$order->set("itemCount",count($items));
		$order->set("subTotal",$this->subTotal->value);
		$order->set("tax",$this->taxTotal->value);
		$order->set("total",$this->total->value);
	}
	
	function checkoutCheck() {
		$errors = array();
		
		$items = $this->getCartItems();
		
		$total = $this->total->getFloat();
		
		$addOnItems = array();
		foreach ($items as $item) {
			$itemOb = new storeItem("OI:".$item);
			$item = $itemOb->get();
						
			if (setAndTrue($item,"addon")) {
				if (!isset($addOnItems[$itemOb->cartItem["addonParent"]])) $addOnItems[$itemOb->cartItem["addonParent"]] = array("items"=>array(),"total"=>0);
				$addOnItems[$itemOb->cartItem["addonParent"]]["items"][] = array(
					"item"=>$item,
					"cartItem"=>$itemOb->cartItem
				);
				$addOnItems[$itemOb->cartItem["addonParent"]]["total"] += $item["priceUSD"];
			} else {
				if (!isset($addOnItems[$itemOb->cartItem["id"]])) $addOnItems[$itemOb->cartItem["id"]] = array("items"=>array(),"total"=>0);
				$addOnItems[$itemOb->cartItem["id"]]["item"] = $item;
				$addOnItems[$itemOb->cartItem["id"]]["cartItem"] = $itemOb->cartItem;
				$addOnItems[$itemOb->cartItem["id"]]["total"] += $item["priceUSD"];
			}
		}
		
		if (isset($this->cfg["addonThreshold"])) {
			foreach ($addOnItems as $o) {
				if ($o["total"] == 0 && $this->cfg["addonThreshold"] == 0) {
					$errors[] = array(
						"message"=>"The '".$o["item"]["name"]."' package must contain at least one item",
						"relatedItem"=>$o["item"]
					);
				} elseif ($o["total"] < $this->cfg["addonThreshold"]) {
					$errors[] = array(
						"message"=>"The '".$o["item"]["name"]."' package must be at least $".sprintf("%01.2f",valueCurrency::convert($this->cfg["addonThreshold"]." USD", "USD")).". Please add more items to this package.",
						"relatedItem"=>$o["item"]
					);
				}
			}
		}
		return count($errors) ? $errors : false;	
	}
	
	function showCart($templateFile = false) {
		if ($templateFile === false) $templateFile = $this->dir."/template/item-cart.php";
		
		$items = $this->getCartItems();

		foreach ($items as $item) {
			$itemOb = new storeItem("OI:".$item);
			$item = $itemOb->get();
			if (setAndTrue($itemOb->cartItem,'addonParent')) continue;
			$cartItem = $itemOb->cartItem;
			$cartItem["message"] = array();
			foreach ($cartItem as $n=>$v) {
				$msg = array();
				if (isset($this->cartErrors[$cartItem["id"]][$n])) $msg[] = '<div class="error">'.implode('<br>', $this->cartErrors[$cartItem["id"]][$n]).'</div>';
				$cartItem["message"][$n] = implode("\n", $msg);
			}
			require($templateFile);
		}
		
		return count($items);
	}
}
