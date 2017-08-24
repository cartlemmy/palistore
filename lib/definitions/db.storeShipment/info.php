<?php

require_once(SL_INCLUDE_PATH.'/class.slValue.php');

class db_storeShipment extends slDBDefinition {
	/*private function updateName(&$items) {
		$items["name"] = valueToString($items["address"],"db/storeShipment/address");
	}
	
	public function update_address($v,&$items,$tableInfo) {
		$this->updateName($items);
	}
	
	public function update_shipDate($v,&$items,$tableInfo) {
		$this->updateName($items);
	}*/
	
	
	public function update_status($v,&$items,$tableInfo) {
		$info = $GLOBALS["slCore"]->db->getTableInfo("db/storeOrderItems");
		if (isset($info["fields"]["status"]["options"][$v])) {
			$GLOBALS["slCore"]->db->update("db/storeOrderItems", array("status"=>$v),array("orderId"=>$items["orderId"]));
		}
	}
	
	public function getDefinition() {
		return array(
			"name"=>"en-us|Shipments",
			"singleName"=>"en-us|Shipment",
			"table"=>"db/storeShipment",
			"key"=>"id",
			"displayName"=>array("item.name"),
			"nameField"=>"name",
			"orderby"=>"shipDate",
			"orderdir"=>"desc",
			"disableDelete"=>true,
			"oldData"=>1,
			"conform"=>array("storeInstance"=>1),
			"disableDelete"=>true,
			"where"=>"(`status`!='in-cart' AND `status`!='')",
			"queryFilters"=>array(
				"ordered"=>array(
					"label"=>"en-us|Payment Approved",
					"where"=>"`status`='ordered'"
				),
				"ready"=>array(
					"label"=>"en-us|Ready",
					"where"=>"`status`='ready' OR `status`='prep-ready' OR `status`='deliv-ready'"
				),
				"in-progress"=>array(
					"label"=>"en-us|In Progress",
					"where"=>"`status`='multi' OR `status`='delivering'"
				),
				"delivered"=>array(
					"label"=>"en-us|Delivered",
					"where"=>"`status`='delivered'"
				),
				"pos"=>array(
					"label"=>"en-us|Needs POS entry",
					"where"=>"(`status`!='payment-failed' AND `status`!='cancelled' AND `status`!='' AND `status`!='refunded') AND `posEntered`=0"
				),
				"payment-failed"=>array(
					"label"=>"en-us|Payment Failed",
					"where"=>"`status`='payment-failed'"
				)
			),
			"fields"=>array(
				"orderId"=>array(
					"label"=>"en-us|Order #",
					"type"=>"object",
					"ref"=>"db/storeOrders",
					"useID"=>1
				),
				"name"=>array(
					"label"=>"en-us|Name",
					"type"=>"text"
				),
				"address"=>array(
					"label"=>"en-us|Address",
					"type"=>"object",
					"ref"=>"db/address",
					"useID"=>1
				),
				"status"=>array(
					"label"=>"en-us|Status",
					"type"=>"select",
					"default"=>"",
					"dependency"=>"id",
					"options"=>array(
						""=>"en-us|New",
						"ordered"=>"en-us|Ordered",
						"prep-ready"=>"en-us|Ready for Preparation",
						"deliv-ready"=>"en-us|Ready for Delivery",
						"ready"=>"en-us|Ready",
						"multi"=>"en-us|Varying Item Statuses",
						"delivering"=>"en-us|Delivery in Progress",
						"delivered"=>"en-us|Delivered",
						"payment-failed"=>"en-us|Payment Failed",
						"cancelled"=>"en-us|Cancelled",
						"refunded"=>"en-us|Refunded"
					),
					"massChange"=>true
				),
				"shipDate"=>array(
					"label"=>"en-us|Ship Date",
					"type"=>"date"
				),
				"paliSession"=>array(
					"label"=>"en-us|Pali Session",
					"type"=>"objectDropDown",
					"ref"=>"db/paliSessions",
					"useID"=>1
				),
			)
		);
	}
}
