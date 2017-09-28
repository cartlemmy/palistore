<?php

require_once(SL_INCLUDE_PATH."/class.slValue.php");
require_once(SL_INCLUDE_PATH."/value/class.valueCurrency.php");
require_once(SL_WEB_PATH."/inc/store/class.storeItem.php");
require_once(SL_WEB_PATH."/inc/store/class.storeCart.php");

class storeOrder extends slClass {
	private $initStatus = "none";
	private $data = array();
	public $id = false;
	public $store = false;
	private $cfg;
	private $token = false;
	public $bypassToken = false;
	public $cart;
	
	function __construct($store,$bypassToken = false, $dontCreate = false) {
		$this->bypassToken = $bypassToken;
		$this->dontCreate = $dontCreate;
		
		$this->cfg = store::getConfig();
		if (is_string($store) && strpos($store,"-") !== false) {
			$store = array_pop(explode("=",$store));
			$token = explode("-",$store,2);
			$this->setId($token[0]);
			$this->token = $token[0]."-".$token[1];
			$this->init();
		} else if (is_numeric($store)) {
			$this->setId($store);
			$this->init();
		} elseif (is_object($store) && $this->id = $store->get("cur-order")) {
			$this->store = $store;
			$this->init();
		} else {
			if (is_object($store)) $this->store = $store;
			$this->id = $this->create();
		}
		if (!$this->store) {
			if (isset($GLOBALS["_YP_STORE_OBJ"])) {
				$this->store = $GLOBALS["_YP_STORE_OBJ"];
			} else {	
				$this->store = new store();
			}
		}
		$this->updateItemCount();
	}
	
	private function setId($id) {
		$len = strlen($this->cfg["orderNumberPrefix"]);
		if (substr($id,0,$len) == $this->cfg["orderNumberPrefix"]) {
			$id = substr($id,$len);
		}
		$this->id = $id;
	}
	
	function apply() {
		if ($this->initStatus != $this->data["status"]) {
			
			$this->data["statusUpdateTS"] = time();
			
			$hist = $this->data;
			$hist["orderId"] = $this->data["id"];
			$hist["ts"] = time();
			if (!$GLOBALS["slCronSession"] && isset($GLOBALS["slSession"])) {
				$hist["userId"] = (int)$GLOBALS["slSession"]->getUserData("id");
			}
			unset($hist["id"]);
			
			$GLOBALS["slCore"]->db->insert($this->cfg["table"]["order-history"], $hist);
			
			if ($this->data["status"] == "ordered" || $this->data["status"] == "cancelled" || $this->data["status"] == "refunded") {
				if ($this->data["status"] == "ordered") {
					$this->data["ordered"] = time();
					if ($this->store) $this->store->set("cur-order",0);
				}
				$this->updateCart(array("status"=>$this->data["status"],"ordered"=>$this->data["ordered"]));
			}
			$this->updateAddresses();
			if (in_array($this->data["status"],array("ordered","prep-ready","deliv-ready","payment-failed","cancelled","refunded"))) store::notify("order-status.".$this->data["status"],$this);
		}
		
		$GLOBALS["slCore"]->db->update($this->cfg["table"]["order"],$this->data, array("id"=>$this->id));
	}
	
	public function getShipmentStatus() {
		return $this->updateAddresses(true);
	}
	
	public function updateAddresses($returnShipmentStatus = false) {
		if (!$this->cart) $this->cart = new storeCart($this->id);
		$items = $this->cart->getCartItems();

		$oldShipments = array();
		
		$shipments = array();
		foreach ($items as $item) {
			$itemOb = new storeItem("OI:".$item);
			$item = $itemOb->get();
			$cartItem = $itemOb->cartItem;
			
			$addr = new storeAddress($cartItem);
			$id = $addr->getFullId();
			if (!isset($shipments[$id])) $shipments[$id] = array("items"=>array(),"_SAN"=>$addr->getName(true));
			
			$shipments[$id]["items"][] = $cartItem;
		}
		
		foreach ($shipments as $addressId=>&$sd) {
			$addressId = array_shift(explode('-',$addressId));
			$GLOBALS["slCore"]->db->update(
				$this->cfg["table"]["shipment"],
				array("active"=>0),
				array("orderId"=>$this->data["id"],"address"=>$addressId)
			);
			
			$shipDate = array();
			$shipStatus = array();
			$otherShipData = array();
			foreach ($sd["items"] as $cartItem) {
				$shipDate[] = $cartItem["shipDate"];
				if (!in_array($cartItem["status"], $shipStatus)) $shipStatus[] = $cartItem["status"];
				foreach ($cartItem as $n=>$v) {
					if (!isset($otherShipData[$n])) {
						$otherShipData[$n] = $v;
					} elseif ($otherShipData[$n] !== $v) {
						$otherShipData[$n] = false;
					}
				}
			}	
			foreach ($otherShipData as $n=>$v) {
				if ($v !== false) $sd[$n] = $v;
			}
			
			$deliveryTypeInfo = $this->getDeliveryTypeInfo($sd);
			$itemsCnt = count($sd["items"])." item".(count($sd["items"]) == 1 ? "" : "s");
			$newShipmentData = array(
				"active"=>1,
				"details"=>$itemsCnt."\n".$deliveryTypeInfo["destination"],
				"name"=>$itemsCnt." to ".$sd["_SAN"],
				"orderId"=>$this->data["id"],
				"address"=>$addressId,
				"shipDate"=>min($shipDate),
				"status"=>implode('/',$shipStatus)
			);
			
			if ($res = $GLOBALS["slCore"]->db->select(
				$this->cfg["table"]["shipment"],
				array("orderId"=>$this->data["id"],"address"=>$addressId)
			)) {
				$shipment = $res->fetch();
				$sd = array_merge($sd, $shipment);
				
				$shipmentId = $shipment["id"];
				//TODO only update if changed
				$GLOBALS["slCore"]->db->update(
					$this->cfg["table"]["shipment"],
					$newShipmentData,
					array("orderId"=>$this->data["id"],"address"=>$addressId)
				);
				
			} else {
				$shipmentId = $GLOBALS["slCore"]->db->insert(
					$this->cfg["table"]["shipment"],
					$newShipmentData
				);
				$newShipmentData["id"] = $shipmentId;
			}
						
			foreach ($sd["items"] as $cartItem) {
				if ($cartItem["shipmentId"] != $shipmentId) {
					$this->cart->update(array("shipmentId"=>$shipmentId),$cartItem["id"]);
				}
			}	
			
			$sd = array_merge($sd, $newShipmentData);
			unset($sd);
		}
		return $shipments;
	}

	private function getDeliveryTypeInfo($shipment) {
		$rv = array();
		$destination = array();
		switch ($shipment["deliveryType"]) {
			case "on-site":
				$destination[] = 'On-site delivery';
				if (setAndTrue($shipment,"paliSessionNameFull")) {
					$destination[] = $shipment["paliSessionNameFull"];
				}				
				break;
			
			case "address":	
			default:
				$destination[] = $shipment["shipAddress1"];
				if (isset($shipment["shipAddress2"])) $destination[] = $shipment["shipAddress2"];
				$destination[] = $shipment["shipCity"].", ".$shipment["shipState"]." ".$shipment["shipPostalCode"];
				break;
				
		}
		$rv["destination"] = implode("\n", $destination);
		
		return $rv;
	}
	
	function create() {
		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["order"],  array("session"=>session_id(),"status"=>""))) {
			$this->data = $res->fetch();
	
			$this->store->set("cur-order", $this->data["id"]);
			
			$this->update();
			
			$this->updateCartOrderId($this->data["id"]);
			
			return $this->data["id"];
		} else {
			$this->set("session",session_id());
			
			$id = $GLOBALS["slCore"]->db->insert($this->cfg["table"]["order"], $this->data);
			
			$this->store->set("cur-order", $id);
			
			$this->updateCartOrderId($id);
			$this->update();
			
			return $id;
		}
	}
	
	function update() {
		if (!$this->cart) $this->cart = new storeCart($this->id);
		$this->cart->updateOrder($this);
	}
	
	function updateCart($data) {
		$GLOBALS["slCore"]->db->update($this->cfg["table"]["cart"], $data, array("session"=>session_id(),"orderId"=>(int)$this->id));
	}
	
	function updateCartOrderId($id) {
		$GLOBALS["slCore"]->db->update($this->cfg["table"]["cart"], array("orderId"=>$id), array("session"=>session_id(),"orderId"=>0));
	}
	
	function updateFromItemStatus() {
		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"],array("orderId"=>(int)$this->id))) {
			$status = false;
			while ($item = $res->fetch()) {
				$nStatus = isset($this->statusMap[$item["status"]]) ? $this->statusMap[$item["status"]] : $item["status"];
				if ($status !== false && $nStatus != $status) {
					$status = "multi";
				} else {
					$status = $nStatus;
				}
			}
			$this->data["status"] = $status;
		}
	}

	function getTotal($ct = "USD") {
		return valueCurrency::convert($this->get("total"),$ct); 
	}
	
	function init() {
		$where = $this->token || $this->bypassToken || $GLOBALS["slSession"]->user->hasPermission("store OR super") ? array("id"=>(int)$this->id) : array("id"=>(int)$this->id,"session"=>session_id());

		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["order"],$where)) {
		
			$this->data = $res->fetch();

			if (!$this->bypassToken && $this->token && $this->getOrderToken($this->data["id"], $this->data["session"]) != $this->token) {
				$this->data = null;
				return $this->error("Invalid order token."/*,false,false,"itsupport@palimountain.com"*/);
			}
			$this->initStatus = $this->data["status"];
		} elseif (!$this->token) {
			$this->id = $this->create();
		} else {
			$this->data = null;
			return $this->error("Invalid order id. (#".($this->token?$this->token:$this->id).")"/*,false,false,"itsupport@palimountain.com"*/);
		}
	}
	
	function checkoutCheck() {
		$this->update();
		return $this->cart->checkoutCheck();
	}
	
	function updateItemCount() {
		if ($res = $GLOBALS["slCore"]->db->select($this->cfg["table"]["cart"],array("orderId"=>$this->id),array("select"=>"COUNT(`id`) AS 'cnt'"))) {
			$row = $res->fetch();
			if ($this->data["itemCount"] != $row["cnt"]) {
				$this->set("itemCount", $row["cnt"]);
			}
		}
	}
	
	function isValid() {
		return $this->data != null;
	}
	
	function hasItems() {
		return $this->get("itemCount") > 0;
	}
	
	function isOrdered() {
		if (isset($_GET["force"])) return true;
		if (isset($this->data["status"]) && $this->data["status"]) {
			switch ($this->data["status"]) {
				case "ordered":
				case "prep-ready":
				case "deliv-ready":
				case "ready":
				case "delivering":
				case "delivered":
				case "cancelled":
				case "refunded":
					return true;
			}
		}
		return false;
	}

	function get($n = false, $def = null) {
		if ($n === false) return $this->data;
		if ($this->data) return isset($this->data[$n]) ? $this->data[$n] : $def;
		return $def;
	}
	
	function set($n,$v) {
		$this->data[$n] = $v;
	}
	
	function getOrderToken($id = false, $session_id = false) {
		if ($id === false) $id = $this->get("id");
		if ($session_id === false) $session_id = session_id();
		return substr($this->cfg["orderNumberPrefix"].$id."-".preg_replace("/[^\w\d]+/","",base64_encode(sha1($session_id."-".$id."-order-token",true))),0,20);
	}
	
	function getOrderNumber() {
		return $this->cfg["orderNumberPrefix"].sprintf("%06d",$this->id);
	}
	
	public static function updateOrders() {
		require_once(SL_WEB_PATH."/inc/store/class.storeItem.php");
		
		$cfg = store::getConfig();
		
		//Update status
		if ($res = $GLOBALS["slCore"]->db->select($cfg["table"]["cart"], "`status`='ordered' AND `shipDate`<=".time())) {
			$orders = array();
			while ($row = $res->fetch()) {
				if (!in_array($row["orderId"],$orders)) $orders[] = $row["orderId"];
				$item = new storeItem("OI:".$row["id"]);
				$item->setOI("status","prep-ready");
				$item->apply();				
			}
			
			
			foreach ($orders as $id) {
				$order = new storeOrder($id,true);
				$order->updateFromItemStatus();
				$order->apply();
			}	
		}
		
		//Remove expired in-cart items
		$GLOBALS["slCore"]->db->delete($cfg["table"]["cart"],"`status`='in-cart' AND `expires`<".time());
	}
	
	function sendEmail($subject,$htmlBody) {
		return $this->store->sendEmail($this->data["email"],$this->data["name"],$subject,$htmlBody);
	}
}

class storeAddress {
	private $vars = array(
		"shipAddressName"=>"name",
		"shipAddress1"=>"shipAddress1",
		"shipAddress2"=>"shipAddress2",
		"shipCity"=>"locality",
		"shipState"=>"administrative_area_level_1",
		"shipPostalCode"=>"postal_code",
		"shipCountry"=>"country"
	);
	private $address;
	private $extra;
	
	public $id = false;
	private $subId = false;
	
	public function __construct(&$data) {
		$this->init($data);
	}
	
	public function init(&$data) {
		$this->address = array();
		$this->extra = array();
		
		$this->subId = false;
		$isOnsite = false;
		if (setAndTrue($data,"deliveryType")) {
			switch ($data["deliveryType"]) {
				case "on-site":
					$isOnsite = true;
					$data["shipAddressName"] = $data["camper"];
					if (setAndTrue($data,"paliSession")) {
						$data["paliSessionName"] = $this->getSessionName($data["paliSession"]);
						$data["paliSessionNameFull"] = "Pali Adventures Summer ".substr($data["paliSessionName"],0,4).", Session ".substr($data["paliSessionName"],5);
					} else {
						$data["paliSessionName"] = "N/A";
					}
					$data["shipAddress2"] = $this->subId = isset($data["cuid"]) ? $data["cuid"] : searchify($data["camper"],'').'-'.$data["paliSessionName"];
					$data = array_merge($data, $GLOBALS["_YP_STORE_OBJ"]->getDefaults("storeOrderItems"));
					break;
			}
		}
		$uid = "";
		foreach ($this->vars as $n=>$to) {
			if (isset($data[$n])) {
				if ($to != "name" && !($to == "shipAddress2" && $isOnsite)) $uid .= searchify($data[$n],'');
				$this->address[$to] = $data[$n];
			}
		}
		foreach ($data as $n=>$v) {
			if (!isset($this->vars[$n])) $this->extra[$n] = $v;
		}
		$this->address["uid"] = strlen($uid) ? substr(preg_replace('/[^0-9A-Za-z]/','',base64_encode(md5($uid, true))), 0, 20) : false;
		
		$this->id = $isOnsite ? 4 : $GLOBALS["slCore"]->db->upsert('db/address', $this->address, array('uid'=>$this->address["uid"]));
	}
	
	private function getSessionName($id) {
		$id = (int)$id;
		if ($id) {
			if ($res = $GLOBALS["slCore"]->db->select('db/paliSessions',array('id'=>$id))) {
				$sess = $res->fetch();
				if (preg_match('/session\s+([\d]{1,2})/i', $sess["name"], $match)) {
					return date('Y',$sess["startDate"]).'S'.$match[1];
				}
			}
		}
		return '0';
	}
	
	public function getName($noInternalLabel = false) {
		if (isset($this->extra["paliSessionName"])) return $this->extra["camper"].' in Session '.$this->extra["paliSessionName"];
		return $noInternalLabel ? $this->address["shipAddress1"] : $this->address["name"];
	}
	
	public function getFullId() {
		return $this->id.($this->subId !== false ? '-'.$this->subId : '');		
	}
}
