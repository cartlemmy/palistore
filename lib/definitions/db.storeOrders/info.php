<?php

function db_storeOrders_status_update($v,&$items,$tableInfo) {
	$info = $GLOBALS["slCore"]->db->getTableInfo("db/storeOrderItems");
	if (isset($info["fields"]["status"]["options"][$v])) {
		$GLOBALS["slCore"]->db->update("db/storeOrderItems", array("status"=>$v),array("orderId"=>$items["id"]));
	}
}

return array(
	"coreTable"=>true,
	"name"=>"en-us|Orders",
	"singleName"=>"en-us|Order",
	"table"=>"db/storeOrders",
	"key"=>"id",
	"displayName"=>array("item.name+' #'+item.id"),
	"nameField"=>"id",
	"orderby"=>"created",
	"orderdir"=>"desc",
	"customEdit"=>"store/order",
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
		"id"=>array(
			"label"=>"en-us|Order #",
			"type"=>"number"
		),
		"created"=>array(
			"label"=>"en-us|Created",
			"type"=>"date",
			"readOnlyField"=>true,
			"viewable"=>false
		),
		"ordered"=>array(
			"label"=>"en-us|Ordered",
			"type"=>"date",
			"readOnlyField"=>true
		),
		"statusUpdateTS"=>array(
			"label"=>"en-us|Updated",
			"type"=>"date",
			"readOnlyField"=>true,
			"viewable"=>false
		),
		"name"=>array(
			"label"=>"en-us|Name",
			"type"=>"text"
		),
		"email"=>array(
			"label"=>"en-us|E-mail",
			"type"=>"email"
		),
		"phone"=>array(
			"label"=>"en-us|Phone",
			"type"=>"text"
		),
		"parentName"=>array(
			"label"=>"en-us|Parent Name",
			"type"=>"text",
			"viewable"=>false
		),
		"subTotal"=>array(
			"label"=>"en-us|Sub Total",
			"type"=>"currency",
			"readOnlyField"=>true,
			"viewable"=>false
		),
		"tax"=>array(
			"label"=>"en-us|Tax",
			"type"=>"currency",
			"readOnlyField"=>true,
			"viewable"=>false
		),
		"total"=>array(
			"label"=>"en-us|Total",
			"type"=>"currency",
			"readOnlyField"=>true
		),
		"itemCount"=>array(
			"label"=>"en-us|Item Count",
			"type"=>"number",
			"editable"=>false,
			"viewable"=>false
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
			"massChange"=>true,
			"updateFunction"=>"db_storeOrders_status_update"
		),
		"posEntered"=>array(
			"label"=>"en-us|Entered in POS",
			"type"=>"select",
			"indexIsValue"=>true,
			"options"=>array(
				"en-us|no",
				"en-us|ENTERED"
			),
			"massChange"=>true
		)
	)
);
