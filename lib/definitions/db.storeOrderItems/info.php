<?php

function db_storeOrderItems_update($newData,$oldData,$info) {
	if (isset($newData["_KEY"]) ) {
		if (isset($oldData["status"]) && $oldData["status"] != "ordered" && $newData["status"] == "ordered") {
			if ($res = $GLOBALS["slCore"]->db->select("db/storeItems",array("id"=>$newData["item"]))) {
				$p = $res->fetch();
				if ($p["optionParent"]) {
					$GLOBALS["slCore"]->db->update("db/storeItems", array("sold"=>array("+=",1)),array("id"=>$p["optionParent"]));
				}
			}
			$GLOBALS["slCore"]->db->update("db/storeItems", array("sold"=>array("+=",1)),array("id"=>$newData["item"]));
		}
		$hist = $newData;
		foreach ($hist as $n=>$v) {
			if ($v == (isset($oldData[$n]) ? $oldData[$n] : "")) unset($hist[$n]);
		}
		unset($hist["_KEY"]);
		if (isset($hist["expires"])) unset($hist["expires"]);
		
		if ($hist) {
			$hist["orderItemId"] = $newData["_KEY"];
			$hist["status"] = isset($newData["status"]) ? $newData["status"] : $oldData["status"];
			
			$hist["ts"] = time();
			if (!$GLOBALS["slCronSession"] && isset($GLOBALS["slSession"])) {
				$hist["userId"] = (int)$GLOBALS["slSession"]->getUserData("id");
			}
			if (isset($hist["id"])) unset($hist["id"]);
			$GLOBALS["slCore"]->db->insert("db/storeOrderItemHistory", $hist);
		}
	}
}
			
return array(
	"coreTable"=>true,
	"name"=>"en-us|Order Items",
	"singleName"=>"en-us|Order Item",
	"table"=>"db/storeOrderItems",
	"key"=>"id",
	"displayName"=>array("item.itemName"),
	"nameField"=>"itemName",
	"orderby"=>"added",
	"orderdir"=>"desc",
	"customEdit"=>"store/order-item",
	"disableDelete"=>true,
	"oldData"=>1,
	"updateFunction"=>"db_storeOrderItems_update",
	"queryFilters"=>array(
		"ordered"=>array(
			"label"=>"en-us|Payment Approved",
			"where"=>"`status`='ordered'"
		),
		"prep-ready"=>array(
			"label"=>"en-us|Prep Ready",
			"where"=>"`status`='prep-ready'"
		),
		"deliv-ready"=>array(
			"label"=>"en-us|Ready for Delivery",
			"where"=>"`status`='deliv-ready'"
		),
		"delivering"=>array(
			"label"=>"en-us|Delivery in Progress",
			"where"=>"`status`='delivering'"
		),
		"delivered"=>array(
			"label"=>"en-us|Delivered",
			"where"=>"`status`='delivered'"
		),
		"cancelled"=>array(
			"label"=>"en-us|Cancelled",
			"where"=>"`status`='cancelled'"
		)
	),
	"fields"=>array(
		"added"=>array(
			"label"=>"en-us|Added",
			"type"=>"date"
		),
		"item"=>array(
			"label"=>"en-us|Item",
			"type"=>"objectDropDown",
			"ref"=>"db/storeItems",
			"useID"=>1
		),
		"price"=>array(
			"label"=>"en-us|Price",
			"type"=>"currency"
		),
		"orderId"=>array(
			"label"=>"en-us|Order #",
			"type"=>"object",
			"ref"=>"db/storeOrders",
			"useID"=>1
		),
		"shipDate"=>array(
			"label"=>"en-us|Ship Date",
			"type"=>"date"
		),
		"status"=>array(
			"label"=>"en-us|Status",
			"type"=>"select",
			"default"=>"in-cart",
			"massChange"=>true,
			"options"=>array(
				"in-cart"=>"en-us|In Cart",
				"ordered"=>"en-us|Ordered",
				"prep-ready"=>"en-us|Ready for Preparation",
				"deliv-ready"=>"en-us|Ready for Delivery",
				"delivering"=>"en-us|Delivery in Progress",
				"delivered"=>"en-us|Delivered",
				"cancelled"=>"en-us|Cancelled",
				"refunded"=>"en-us|Refunded"
			)
		),
		"paliSession"=>array(
			"label"=>"en-us|Pali Session",
			"type"=>"objectDropDown",
			"ref"=>"db/paliSessions",
			"useID"=>1
		),
		"camperNote"=>array(
			"label"=>"en-us|Camper Note",
			"type"=>"textarea",
			"viewable"=>false
		)
	)
);
